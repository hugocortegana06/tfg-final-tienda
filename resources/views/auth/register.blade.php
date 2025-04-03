@extends('layouts.app')

@section('title', 'Registro')

@section('content')
<div class="bg-gray-light d-flex justify-content-center align-items-center" style="min-height: 100vh;">
  <div class="card shadow fade-in" style="max-width: 500px; width: 100%;">
    <div class="card-body p-4">
      <h2 class="text-center text-light-blue mb-4">Crear Cuenta</h2>

      {{-- Manejo de errores --}}
      @if($errors->any())
        <div class="alert alert-danger">
          @foreach($errors->all() as $error)
            <div>{{ $error }}</div>
          @endforeach
        </div>
      @endif

      <form method="POST" action="{{ route('register.process') }}">
        @csrf
        <div class="mb-3">
          <label for="name" class="form-label">Nombre</label>
          <input type="text" name="name" id="name" class="form-control" required>
        </div>
        <div class="mb-3">
          <label for="email" class="form-label">Correo electrónico</label>
          <input type="email" name="email" id="email" class="form-control" required>
        </div>
        <div class="mb-3">
          <label for="password" class="form-label">Contraseña</label>
          <input type="password" name="password" id="password" class="form-control" required>
        </div>
        <div class="mb-3">
          <label for="password_confirmation" class="form-label">Repite la contraseña</label>
          <input type="password" name="password_confirmation" id="password_confirmation" class="form-control" required>
        </div>

        {{-- Campos extra --}}
        <div class="mb-3">
          <label for="phone" class="form-label">Teléfono</label>
          <input type="text" name="phone" id="phone" class="form-control">
        </div>

        <div class="d-grid">
          <button type="submit" class="btn btn-light-blue">Registrar</button>
        </div>
      </form>

      <div class="text-center mt-3">
        <a href="{{ route('login.form') }}" class="text-decoration-none text-light-blue">
          ¿Ya tienes cuenta? Inicia sesión
        </a>
      </div>
    </div>
  </div>
</div>
@endsection
