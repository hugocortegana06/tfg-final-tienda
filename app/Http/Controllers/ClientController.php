<?php
namespace App\Http\Controllers;

use App\Models\Client;
use Illuminate\Http\Request;

class ClientController extends Controller
{
    // Listado de clientes
    public function index()
    {
        // Asumiendo phone es PK, no forzamos un order especial o sí:
        $clients = Client::orderBy('created_at','desc')->get();
        return view('clients.index', compact('clients'));
    }

    // Muestra formulario de creación
    public function create()
    {
        return view('clients.create');
    }

    // Procesa el formulario
    public function store(Request $request)
    {
        // Validaciones (modifica campos según tu DB)
        $request->validate([
            'phone' => 'required|string|max:20|unique:clients,phone',
            'name' => 'required|string|max:100',
            'surname' => 'required|string|max:100',
            'additional_info' => 'nullable|string'
        ]);

        // Crea el cliente
        Client::create([
            'phone' => $request->phone,
            'name' => $request->name,
            'surname' => $request->surname,
            'additional_info' => $request->additional_info
        ]);

        // Mensaje y redirección
        return redirect()->route('clients.index')
                         ->with('success','Cliente creado correctamente');
    }
    
    public function update(Request $request, Client $client)
{
    // Validar lo que se edita. 
    // Asume phone no cambia, 
    // y se editan name, surname, additional_info
    $request->validate([
        'name' => 'required|string|max:100',
        'surname' => 'required|string|max:100',
        'additional_info' => 'nullable|string'
    ]);

    $client->update([
        'name' => $request->name,
        'surname' => $request->surname,
        'additional_info' => $request->additional_info
    ]);

    // Retorna JSON (para la edición inline)
    return response()->json([
        'success' => true,
        'message' => 'Cliente actualizado'
    ], 200);
}

public function destroy(Client $client)
{
    $client->delete();

    // Redirige al index con un mensaje
    return redirect()->route('clients.index')
                     ->with('success','Cliente eliminado correctamente');
}
    

    // (Opcional) Editar, Actualizar, Eliminar, etc.
    // public function edit(Client $client) { ... }
    // public function update(Request $request, Client $client) { ... }
    // public function destroy(Client $client) { ... }
}
