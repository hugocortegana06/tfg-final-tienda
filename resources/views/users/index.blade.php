@extends('layouts.app')

@section('title','Usuarios')

@section('content')
<div class="container">
    <h1 class="mb-4">Listado de Usuarios</h1>

    <!-- Botón para ir al formulario de añadir usuario -->
    <div class="text-end mb-3">
        <a href="{{ route('users.create') }}" class="btn btn-primary">
            Añadir usuario
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success mb-3">
            {{ session('success') }}
        </div>
    @endif

    @if($users->count() > 0)
        <table class="table table-bordered table-striped align-middle">
            <thead>
                <tr>
                    <th>Nombre</th>
                    <th>Email</th>
                    <th>Fecha Creación</th>
                </tr>
            </thead>
            <tbody>
            @foreach($users as $user)
                <tr>
                    <td>{{ $user->name }}</td>
                    <td>{{ $user->email }}</td>
                    <td>{{ $user->created_at->format('d-m-Y H:i') }}</td>
                </tr>
            @endforeach
            </tbody>
        </table>
    @else
        <p>No hay usuarios registrados.</p>
    @endif
</div>
@endsection
