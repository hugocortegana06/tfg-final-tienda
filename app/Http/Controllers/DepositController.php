<?php

namespace App\Http\Controllers;

use App\Models\Deposit;
use App\Models\Client;
use Illuminate\Http\Request;

class DepositController extends Controller
{
    /**
     * Listar depósitos (excluye Finalizado y Entregado), con búsqueda y paginación.
     */
    public function index(Request $request)
    {
        // 1) Gestionar per_page en sesión
        if ($request->has('per_page')) {
            $pp = (int)$request->per_page;
            if (! in_array($pp, [5, 10, 15, 20])) {
                $pp = 10;
            }
            session(['deposits.per_page' => $pp]);
        }
        $perPage = session('deposits.per_page', 10);

        // 2) Construir query, excluyendo Finalizado y Entregado
        $query = Deposit::with('client')
            ->whereNotIn('status', ['Finalizado','Entregado'])
            ->orderBy('created_at','desc');

        // 3) Filtro live‑search por cliente
        if ($search = $request->search) {
            $query->whereHas('client', function($q) use($search) {
                $q->where('name','like',"%{$search}%")
                  ->orWhere('surname','like',"%{$search}%");
            });
        }

        // 4) Paginación
        $deposits = $query
            ->paginate($perPage)
            ->appends([
                'per_page' => $perPage,
                'search'   => $search,
            ]);

        // 5) Si es AJAX solo devolvemos la tabla parcial
        if ($request->ajax()) {
            return view('deposits.partials.table', compact('deposits'))->render();
        }

        // 6) Vista completa
        return view('deposits.index', compact('deposits','perPage','search'));
    }

    /**
     * Mostrar formulario de creación.
     */
    public function create()
    {
        $clients = Client::orderBy('name')->get();
        return view('deposits.create', compact('clients'));
    }

    /**
     * Almacenar nuevo depósito.
     */
    public function store(Request $request)
    {
        $request->validate([
            'client_phone'        => 'required|exists:clients,phone',
            'brand'               => 'required|string|max:100',
            'model'               => 'required|string|max:100',
            'serial_number'       => 'required|string|max:100',
            'problem_description' => 'required|string',
            'more_info'           => 'nullable|string',
            'unlock_password'     => 'nullable|string',
            'status'              => 'required|in:En curso,Electrónico,Finalizado,Entregado',
        ]);

        Deposit::create([
            'client_phone'        => $request->client_phone,
            'user_id'             => auth()->id(),
            'brand'               => $request->brand,
            'model'               => $request->model,
            'serial_number'       => $request->serial_number,
            'problem_description' => $request->problem_description,
            'more_info'           => $request->more_info,
            'unlock_password'     => $request->unlock_password,
            'status'              => $request->status,
            'date_in'             => now()->toDateString(),
            'date_out'            => null,
        ]);

        return redirect()
            ->route('deposits.index')
            ->with('success','Depósito creado correctamente');
    }

    /**
     * Actualizar inline (solo status, date_out o more_info).
     */
    public function update(Request $request, Deposit $deposit)
    {
        $request->validate([
            'status'    => 'required|in:En curso,Electrónico,Finalizado,Entregado',
            'date_out'  => 'nullable|date',
            'more_info' => 'nullable|string',
        ]);

        $deposit->update($request->only('status','date_out','more_info'));

        return response()->json(['message'=>'Depósito actualizado correctamente']);
    }

    /**
     * Eliminar depósito.
     */
    public function destroy(Deposit $deposit)
    {
        $deposit->delete();
        return redirect()
            ->route('deposits.index')
            ->with('success','Depósito eliminado correctamente');
    }

    // app/Http/Controllers/DepositController.php
    public function finalizados(Request $request)
    {
        // 1) Gestionar per_page en sesión
        if ($request->has('per_page')) {
            $pp = (int)$request->per_page;
            if (!in_array($pp, [5,10,15,20])) {
                $pp = 10;
            }
            session(['deposits.per_page' => $pp]);
        }
        $perPage = session('deposits.per_page', 10);
    
        // 2) Query: sólo estado "Finalizado"
        $query = Deposit::with('client')
            ->where('status', 'Finalizado')
            ->orderBy('created_at', 'desc');
    
        // 3) Live‑search opcional (por nombre/apellidos de cliente)
        if ($search = $request->search) {
            $query->whereHas('client', function($q) use($search) {
                $q->where('name','like',"%{$search}%")
                  ->orWhere('surname','like',"%{$search}%");
            });
        }
    
        // 4) Paginación
        $deposits = $query
            ->paginate($perPage)
            ->appends([
                'per_page' => $perPage,
                'search'   => $search ?? '',
            ]);
    
        // 5) Devolver vista completa
        return view('deposits.finalizados', compact('deposits','perPage','search'));
    }
    
    /**
     *  Sólo la tabla parcial (para recarga AJAX).
     */
    public function finalizadosPartial(Request $request)
    {
        // exactamente la misma lógica de filtrado/paginación:
        if ($request->has('per_page')) {
            $pp = (int)$request->per_page;
            if (!in_array($pp, [5,10,15,20])) {
                $pp = 10;
            }
            session(['deposits.per_page' => $pp]);
        }
        $perPage = session('deposits.per_page', 10);
    
        $query = Deposit::with('client')
            ->where('status', 'Finalizado')
            ->orderBy('created_at', 'desc');
    
        if ($search = $request->search) {
            $query->whereHas('client', function($q) use($search) {
                $q->where('name','like',"%{$search}%")
                  ->orWhere('surname','like',"%{$search}%");
            });
        }
    
        $deposits = $query
            ->paginate($perPage)
            ->appends([
                'per_page' => $perPage,
                'search'   => $search ?? '',
            ]);
    
        // sólo devolvemos el partial que ya tienes en resources/views/deposits/partials/table.blade.php
        return view('deposits.partials.table', compact('deposits'))->render();
    }
}
