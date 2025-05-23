<?php

namespace App\Http\Controllers;

use App\Models\Deposit;
use App\Models\Client;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Validator;

class DepositController extends Controller
{
    /**
     * 1) Listar depósitos activos (excluye Finalizado y Entregado),
     *    con búsqueda por cliente, filtro de garantía y paginación.
     */
    public function index(Request $request)
    {
        // per_page en sesión
        if ($request->has('per_page')) {
            $pp = (int)$request->per_page;
            if (! in_array($pp, [5,10,15,20])) {
                $pp = 10;
            }
            session(['deposits.per_page' => $pp]);
        }
        $perPage = session('deposits.per_page', 10);

        // Query base
        $query = Deposit::with(['client','creator','lastModifier'])
            ->whereNotIn('status', ['Finalizado','Entregado'])
            ->orderBy('created_at','desc');

        // Filtro live-search por cliente (nombre, apellidos, nombre completo)
        if ($search = $request->get('search')) {
            $query->whereHas('client', function($q) use($search) {
                $q->where('name','like',"%{$search}%")
                  ->orWhere('surname','like',"%{$search}%")
                  ->orWhereRaw("CONCAT(name, ' ', surname) LIKE ?", ["%{$search}%"]);
            });
        }

        // Filtro “sólo garantía”
        $warranty = $request->get('warranty');
        if ($warranty) {
            $query->where('under_warranty', 1);
        }

        // Paginación (conservamos search, per_page y warranty)
        $deposits = $query
            ->paginate($perPage)
            ->appends([
                'per_page' => $perPage,
                'search'   => $search,
                'warranty' => $warranty,
            ]);

        // Si es AJAX devolvemos sólo la tabla partial
        if ($request->ajax()) {
            return view('deposits.partials.table', compact('deposits'))->render();
        }

        // Vista completa
        return view('deposits.index', compact('deposits','perPage','search','warranty'));
    }

    /**
     * 2) Formulario de creación.
     */
    public function create()
    {
        $clients = Client::orderBy('name')->get();
        return view('deposits.create', compact('clients'));
    }

    /**
     * 3) Almacenar nuevo depósito.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'client_phone'        => 'required|exists:clients,phone',
            'brand'               => 'required|string|max:100',
            'model'               => 'required|string|max:100',
            'serial_number'       => 'required|string|max:100',
            'problem_description' => 'required|string',
            'more_info'           => 'nullable|string',
            'unlock_password'     => 'nullable|string',
            'budget'              => 'nullable|numeric',
            'pin_or_password'     => 'nullable|string|max:255',
            'work_notes'          => 'nullable|string',
            'under_warranty'      => 'sometimes|boolean',
            'status'              => 'required|in:En curso,Electrónico,Finalizado,Entregado',
        ]);

        if ($validator->fails()) {
            // Redirigimos siempre a la pantalla de creación con los errores y old()
            return redirect()->route('deposits.create')
                ->withErrors($validator)
                ->withInput();
        }

        $data = $validator->validated();

        // Asignar creadores y fechas
        $data['user_id']                   = auth()->id();
        $data['last_modification_user_id'] = auth()->id();
        $data['date_in']                   = now()->toDateString();
        $data['date_out']                  = null;
        $data['under_warranty']            = $request->boolean('under_warranty');

        Deposit::create($data);

        return redirect()
            ->route('deposits.index')
            ->with('success','Depósito creado correctamente');
    }

    /**
     * 4) Inline‐update (status, date_out, more_info, budget…).
     */
    public function update(Request $request, Deposit $deposit)
    {
        $data = $request->validate([
            'status'            => 'required|in:En curso,Electrónico,Finalizado,Entregado',
            'date_out'          => 'nullable|date',
            'more_info'         => 'nullable|string',
            'budget'            => 'nullable|numeric',
            'pin_or_password'   => 'nullable|string|max:255',
            'work_notes'        => 'nullable|string',
            'under_warranty'    => 'sometimes|boolean',
        ]);
        $data['under_warranty'] = $request->boolean('under_warranty');

        // Si cambia a "Entregado" y no tenía date_out, lo fijamos hoy
        if ($data['status'] === 'Entregado' && is_null($deposit->date_out)) {
            $data['date_out'] = now()->toDateString();
        }

        // Registrar quién modificó por última vez
        $data['last_modification_user_id'] = auth()->id();

        $deposit->update($data);

        return response()->json(['message' => 'Depósito actualizado correctamente']);
    }

    /**
     * 5) Eliminar depósito.
     */
    public function destroy(Deposit $deposit)
    {
        $deposit->delete();

        return redirect()
            ->back()
            ->with('success','Depósito eliminado correctamente');
    }

    /**
     * 6) Página de “Finalizados”.
     */
    public function finalizados(Request $request)
    {
        // lógica idéntica de per_page + búsqueda…
        if ($request->has('per_page')) {
            $pp = (int)$request->per_page;
            if (! in_array($pp, [5,10,15,20])) $pp = 10;
            session(['deposits.per_page' => $pp]);
        }
        $perPage = session('deposits.per_page', 10);

        $query = Deposit::with(['client','creator','lastModifier'])
            ->where('status','Finalizado')
            ->orderBy('created_at','desc');

        if ($search = $request->get('search')) {
            $query->whereHas('client', function($q) use($search) {
                $q->where('name','like',"%{$search}%")
                  ->orWhere('surname','like',"%{$search}%")
                  ->orWhereRaw("CONCAT(name, ' ', surname) LIKE ?", ["%{$search}%"]);
            });
        }

        $deposits = $query
            ->paginate($perPage)
            ->appends(['per_page'=>$perPage,'search'=>$search]);

        return view('deposits.finalizados', compact('deposits','perPage','search'));
    }

    /**
     * 7) Partial AJAX para “Finalizados”.
     */
    public function finalizadosPartial(Request $request)
    {
        // misma lógica de finalizados()
        if ($request->has('per_page')) {
            $pp = (int)$request->per_page;
            if (! in_array($pp, [5,10,15,20])) $pp = 10;
            session(['deposits.per_page' => $pp]);
        }
        $perPage = session('deposits.per_page', 10);

        $query = Deposit::with(['client','creator','lastModifier'])
            ->where('status','Finalizado')
            ->orderBy('created_at','desc');

        if ($search = $request->get('search')) {
            $query->whereHas('client', function($q) use($search) {
                $q->where('name','like',"%{$search}%")
                  ->orWhere('surname','like',"%{$search}%")
                  ->orWhereRaw("CONCAT(name, ' ', surname) LIKE ?", ["%{$search}%"]);
            });
        }

        $deposits = $query
            ->paginate($perPage)
            ->appends(['per_page'=>$perPage,'search'=>$search]);

        return view('deposits.partials.table_finalizados', compact('deposits'))->render();
    }

    /**
     * 8) Página de “Entregados”.
     */
    public function entregados(Request $request)
    {
        // lógica idéntica de entregados()
        if ($request->has('per_page')) {
            $pp = (int)$request->per_page;
            if (! in_array($pp, [5,10,15,20])) $pp = 10;
            session(['deposits.per_page' => $pp]);
        }
        $perPage = session('deposits.per_page', 10);

        $query = Deposit::with(['client','creator','lastModifier'])
            ->where('status','Entregado')
            ->orderBy('created_at','desc');

        if ($search = $request->get('search')) {
            $query->whereHas('client', function($q) use($search) {
                $q->where('name','like',"%{$search}%")
                  ->orWhere('surname','like',"%{$search}%")
                  ->orWhereRaw("CONCAT(name, ' ', surname) LIKE ?", ["%{$search}%"]);
            });
        }

        $deposits = $query
            ->paginate($perPage)
            ->appends(['per_page'=>$perPage,'search'=>$search]);

        return view('deposits.entregados', compact('deposits','perPage','search'));
    }

    /**
     * 9) Partial AJAX para “Entregados”.
     */
    public function entregadosPartial(Request $request)
    {
        // misma lógica de entregados()
        if ($request->has('per_page')) {
            $pp = (int)$request->per_page;
            if (! in_array($pp, [5,10,15,20])) $pp = 10;
            session(['deposits.per_page' => $pp]);
        }
        $perPage = session('deposits.per_page', 10);

        $query = Deposit::with(['client','creator','lastModifier'])
            ->where('status','Entregado')
            ->orderBy('created_at','desc');

        if ($search = $request->get('search')) {
            $query->whereHas('client', function($q) use($search) {
                $q->where('name','like',"%{$search}%")
                  ->orWhere('surname','like',"%{$search}%")
                  ->orWhereRaw("CONCAT(name, ' ', surname) LIKE ?", ["%{$search}%"]);
            });
        }

        $deposits = $query
            ->paginate($perPage)
            ->appends(['per_page'=>$perPage,'search'=>$search]);

        return view('deposits.partials.table_entregados', compact('deposits'))->render();
    }

    /**
     * 10) Generar etiqueta (PDF).
     */
    public function label(Deposit $deposit)
    {
        $pdf = Pdf::loadView('deposits.label', compact('deposit'));
        return $pdf->download("etiqueta_{$deposit->id}.pdf");
    }

    /**
     * 11) Generar factura (PDF).
     */
    public function invoice(Deposit $deposit)
    {
        $pdf = Pdf::loadView('deposits.invoice', compact('deposit'));
        return $pdf->download("factura_{$deposit->id}.pdf");
    }
}
