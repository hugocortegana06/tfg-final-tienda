@extends('layouts.app')

@section('title', 'Crear Usuario')

@section('content')
<div class="container">
    <h1 class="mb-4">Añadir Usuario</h1>

    {{-- Alerta de éxito --}}
    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    {{-- Mostrar errores de validación del servidor --}}
    @if($errors->any())
        <div class="alert alert-danger">
            @foreach ($errors->all() as $error)
              <div>{{ $error }}</div>
            @endforeach
        </div>
    @endif

    <form id="createUserForm" action="{{ route('users.store') }}" method="POST" novalidate>
        @csrf

        <!-- Nombre -->
        <div class="mb-3">
            <label for="name" class="form-label">Nombre</label>
            <input 
                type="text"
                name="name"
                id="name"
                class="form-control"
                required 
                maxlength="100"
                value="{{ old('name') }}">
        </div>

        <!-- Email -->
        <div class="mb-3">
            <label for="email" class="form-label">Correo Electrónico</label>
            <input 
                type="email"
                name="email"
                id="email"
                class="form-control"
                required
                value="{{ old('email') }}">
        </div>

        <!-- Contraseña -->
        <div class="mb-3">
            <label for="password" class="form-label">Contraseña</label>
            <input 
                type="password"
                name="password"
                id="password"
                class="form-control"
                required 
                minlength="6">
        </div>

        <!-- Confirmar Contraseña -->
        <div class="mb-3">
            <label for="password_confirmation" class="form-label">Confirmar Contraseña</label>
            <input 
                type="password"
                name="password_confirmation"
                id="password_confirmation"
                class="form-control"
                required 
                minlength="6">
        </div>

        <!-- Rol -->
        <div class="mb-3">
            <label for="role" class="form-label">Rol</label>
            <select name="role" id="role" class="form-select" required>
                <option value="">-- Selecciona --</option>
                <option value="admin" {{ old('role') === 'admin' ? 'selected' : '' }}>Admin</option>
                <option value="user"  {{ old('role') === 'user'  ? 'selected' : '' }}>User</option>
            </select>
        </div>

        <button type="submit" class="btn btn-primary w-100">Crear Usuario</button>
    </form>
</div>

{{-- Validación JS --}}
<script>
document.getElementById('createUserForm').addEventListener('submit', function(e) {
    // Ejemplo sencillo de validación JS: confirmar contraseñas coincidan
    const pass = document.getElementById('password').value;
    const passConfirm = document.getElementById('password_confirmation').value;

    if(pass !== passConfirm) {
        e.preventDefault();
        alert('Las contraseñas no coinciden. Por favor, verifica.');
    }
});
</script>
@endsection
