@extends('layouts.app')

@section('title','Dashboard')

@section('content')
<div class="container">
    <h1 class="mb-4 text-center">Panel de Control</h1>

    <!-- Sección de botones arriba -->
    <div class="d-flex justify-content-center mb-4">
        @if(Auth::check() && Auth::user()->role === 'admin')
            <!-- Botón para añadir usuarios (solo para admin) -->
            <a href="{{ route('users.index') }}" class="btn btn-danger">
            Añadir usuarios
            </a>
        @endif

        <!-- Botón para añadir depósito (visible para todos los roles logueados) -->
        <a href="{{ route('deposits.create') }}" class="btn btn-primary">
            Añadir depósito
        </a>
    </div>

    <div class="row g-3">
        <!-- Widget 1: Depósitos Finalizados -->
        <div class="col-md-4">
            <div class="card text-center shadow-sm">
                <div class="card-body">
                    <h5 class="card-title mb-3">Depósitos Finalizados</h5>
                    <p class="card-text">
                        Consulta todos los depósitos que han sido completados.
                    </p>
                    <a href="{{ route('deposits.finalizados') }}" class="btn btn-success">
                        Ver Depósitos Finalizados
                    </a>
                </div>
            </div>
        </div>

        <!-- Widget 2: Depósitos en Curso -->
        <div class="col-md-4">
            <div class="card text-center shadow-sm">
                <div class="card-body">
                    <h5 class="card-title mb-3">Depósitos en Curso</h5>
                    <p class="card-text">
                        Observa los depósitos que aún están pendientes de resolver.
                    </p>
                    <a href="{{ route('deposits.index') }}" class="btn btn-warning">
                        Ver Depósitos en Curso
                    </a>
                </div>
            </div>
        </div>

        <!-- Widget 3: Gestionar Clientes -->
        <div class="col-md-4">
            <div class="card text-center shadow-sm">
                <div class="card-body">
                    <h5 class="card-title mb-3">Gestionar Clientes</h5>
                    <p class="card-text">
                        Visualiza y administra el listado de clientes.
                    </p>
                    <a href="{{ route('clients.index') }}" class="btn btn-primary">
                        Ver / Gestionar Clientes
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
