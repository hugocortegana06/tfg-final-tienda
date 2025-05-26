<?php

namespace App\Http\Controllers;

use App\Models\Client;
use Illuminate\Http\Request;

class ClientController extends Controller
{
    /**
     * Mostrar listado de clientes con paginación,
     * control de per-page y búsqueda por nombre/apellidos/nombre completo.
     */
    public function index(Request $request)
    {
        // 1. Leer per_page de la query o de la sesión (default 10)
        $perPage = $request->query('per_page', session('clients.per_page', 10));

        // 2. Si viene per_page en la URL, validarlo y guardarlo en sesión
        if ($request->has('per_page')) {
            $pp = (int) $request->query('per_page');
            if (! in_array($pp, [5, 10, 15, 20])) {
                $pp = 10;
            }
            session(['clients.per_page' => $pp]);
            $perPage = $pp;
        }

        // 3. Leer término de búsqueda
        $search = $request->query('search', '');

        // 4. Construir la consulta con filtro condicional y ordenación
        $query = Client::orderBy('created_at', 'desc')
            ->when($search, function($q) use ($search) {
                $q->where(function($qq) use ($search) {
                    $qq->where('name', 'LIKE', "%{$search}%")
                       ->orWhere('surname', 'LIKE', "%{$search}%")
                       ->orWhereRaw("CONCAT(name, ' ', surname) LIKE ?", ["%{$search}%"]);
                });
            });

        // 5. Paginación y preservación de query string
        $clients = $query
            ->paginate($perPage)
            ->appends([
                'per_page' => $perPage,
                'search'   => $search,
            ]);

        // 6. Si la petición es AJAX, devolvemos solo el partial de la tabla
        if ($request->ajax()) {
            return view('clients.partials.table', compact('clients', 'perPage', 'search'))->render();
        }

        // 7. Vista completa para peticiones normales
        return view('clients.index', compact('clients', 'perPage', 'search'));
    }

    /**
     * Mostrar formulario para crear un nuevo cliente.
     */
    public function create()
    {
        return view('clients.create');
    }

    /**
     * Almacenar un nuevo cliente en la base de datos.
     */
    public function store(Request $request)
    {
        $request->validate([
            'phone'           => 'required|string|max:20|unique:clients,phone',
            'phone_2'         => 'nullable|string|max:20',
            'name'            => 'required|string|max:100',
            'surname'         => 'required|string|max:100',
            'additional_info' => 'nullable|string',
        ]);

        Client::create([
            'phone'           => $request->phone,
            'phone_2'         => $request->phone_2,
            'name'            => $request->name,
            'surname'         => $request->surname,
            'additional_info' => $request->additional_info,
        ]);

        return redirect()
            ->route('clients.index')
            ->with('success', 'Cliente creado correctamente');
    }

    /**
     * Mostrar formulario para editar un cliente existente.
     */
    public function edit(Client $client)
    {
        return view('clients.edit', compact('client'));
    }

    /**
     * Actualizar los datos de un cliente.
     */
    public function update(Request $request, Client $client)
    {
        $request->validate([
            'phone_2'         => 'nullable|string|max:20',
            'name'            => 'required|string|max:100',
            'surname'         => 'required|string|max:100',
            'additional_info' => 'nullable|string',
        ]);

        $client->update([
            'phone_2'         => $request->phone_2,
            'name'            => $request->name,
            'surname'         => $request->surname,
            'additional_info' => $request->additional_info,
        ]);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Cliente actualizado correctamente',
            ]);
        }

        return redirect()
            ->route('clients.index')
            ->with('success', 'Cliente actualizado correctamente');
    }

    /**
     * Eliminar un cliente.
     */
    public function destroy(Client $client)
    {
        $client->delete();

        return redirect()
            ->route('clients.index')
            ->with('success', 'Cliente eliminado correctamente');
    }

    /**
     * Endpoint para búsqueda vía AJAX (typeahead).
     */
    public function search(Request $request)
    {
        $q = $request->get('query', '');

        $matches = Client::where(function($qq) use ($q) {
                $qq->where('name', 'LIKE', "%{$q}%")
                   ->orWhere('surname', 'LIKE', "%{$q}%")
                   ->orWhereRaw("CONCAT(name, ' ', surname) LIKE ?", ["%{$q}%"]);
            })
            ->limit(5)
            ->get()
            ->map(function($c) {
                return [
                    'phone'           => $c->phone,
                    'phone_2'         => $c->phone_2,
                    'name'            => $c->name,
                    'surname'         => $c->surname,
                    'additional_info' => $c->additional_info,
                    'created_at'      => $c->created_at->format('d-m-Y H:i'),
                ];
            });

        return response()->json($matches);
    }
}
