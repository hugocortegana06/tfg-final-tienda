@extends('layouts.app')

@section('title','Iniciar Sesión')

@section('content')
<div class="d-flex justify-content-center align-items-center" style="min-height: 75vh;">
    <div class="card p-4" style="max-width: 400px; width:100%;">
        <h2 class="text-center mb-4">Iniciar Sesión</h2>

        @if($errors->any())
            <div class="alert alert-danger">
                @foreach($errors->all() as $error)
                  <div>{{ $error }}</div>
                @endforeach
            </div>
        @endif

        <form action="{{ route('login.process') }}" method="POST">
            @csrf
            <div class="mb-3">
                <label for="email" class="form-label">Correo electrónico</label>
                <input type="email" name="email" id="email"
                       class="form-control" required autofocus>
            </div>
            <div class="mb-3">
                <label for="password" class="form-label">Contraseña</label>
                <input type="password" name="password" id="password"
                       class="form-control" required>
            </div>
            <button type="submit" class="btn btn-primary w-100">Entrar</button>
        </form>
    </div>
</div>
@endsection
