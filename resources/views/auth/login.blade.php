@extends('layouts.app')

@section('title', 'Iniciar Sesión')

@section('content')
<div class="bg-gray-light d-flex justify-content-center align-items-center" style="min-height: 100vh;">
  <div class="card shadow fade-in" style="max-width: 400px; width: 100%;">
    <div class="card-body p-4">
      <h2 class="text-center text-light-blue mb-4">Iniciar Sesión</h2>

      {{-- Manejo de errores --}}
      @if($errors->any())
        <div class="alert alert-danger">
          @foreach($errors->all() as $error)
            <div>{{ $error }}</div>
          @endforeach
        </div>
      @endif

      <form method="POST" action="{{ route('login.process') }}">
        @csrf
        <div class="mb-3">
          <label for="email" class="form-label">Correo electrónico</label>
          <input type="email" name="email" id="email" class="form-control" required autofocus>
        </div>
        <div class="mb-3">
          <label for="password" class="form-label">Contraseña</label>
          <input type="password" name="password" id="password" class="form-control" required>
        </div>

        <div class="d-grid">
          <button type="submit" class="btn btn-light-blue">Iniciar sesión</button>
        </div>
      </form>

      {{-- Enlace al registro --}}
      <div class="text-center mt-3">
        <a href="{{ route('register.form') }}" class="text-decoration-none text-light-blue">
          ¿No tienes cuenta? Regístrate
        </a>
      </div>
    </div>
  </div>
</div>
@endsection
