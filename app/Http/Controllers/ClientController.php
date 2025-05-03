<?php

namespace App\Http\Controllers;

use App\Models\Client;
use Illuminate\Http\Request;

class ClientController extends Controller
{
    /**
     * Mostrar listado de clientes con paginación y control de per-page.
     */
    public function index(Request $request)
    {
        // 1. Obtener per_page de la query o de la sesión, con valor por defecto 10
        $perPage = $request->query('per_page', session('clients.per_page', 10));

        // 2. Si viene per_page en la URL, lo validamos y guardamos en sesión
        if ($request->has('per_page')) {
            $pp = (int) $request->query('per_page');
            if (! in_array($pp, [5, 10, 15, 20])) {
                $pp = 10;
            }
            session(['clients.per_page' => $pp]);
            $perPage = $pp;
        }

        // 3. Paginamos y mantenemos per_page en los links
        $clients = Client::orderBy('created_at', 'desc')
                         ->paginate($perPage)
                         ->appends(['per_page' => $perPage]);

        return view('clients.index', compact('clients', 'perPage'));
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
        // Validaciones
        $request->validate([
            'phone'           => 'required|string|max:20|unique:clients,phone',
            'phone_2'         => 'nullable|string|max:20',                   // ← nuevo campo
            'name'            => 'required|string|max:100',
            'surname'         => 'required|string|max:100',
            'additional_info' => 'nullable|string'
        ]);

        Client::create([
            'phone'           => $request->phone,
            'phone_2'         => $request->phone_2,                         // ← asignamos
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
        // Validamos también phone_2
        $request->validate([
            'phone_2'         => 'nullable|string|max:20',
            'name'            => 'required|string|max:100',
            'surname'         => 'required|string|max:100',
            'additional_info' => 'nullable|string'
        ]);

        $client->update([
            'phone_2'         => $request->phone_2,                         // ← actualizamos
            'name'            => $request->name,
            'surname'         => $request->surname,
            'additional_info' => $request->additional_info,
        ]);

        // Si la petición espera JSON, devolvemos JSON
        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Cliente actualizado correctamente'
            ]);
        }

        // Si no, redirigimos normalmente
        return redirect()
            ->route('clients.index')
            ->with('success', 'Cliente actualizado correctamente');
    }

    /**
     * Endpoint para búsqueda vía AJAX (typeahead).
     */
    public function search(Request $request)
    {
        $q = $request->get('query', '');

        $matches = Client::where('name', 'like', "%{$q}%")
            ->orWhere('surname', 'like', "%{$q}%")
            ->limit(5)
            ->get(['phone', 'phone_2', 'name', 'surname']);         // ← incluimos phone_2

        return response()->json($matches);
    }
}
