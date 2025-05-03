{{-- resources/views/clients/create.blade.php --}}
@extends('layouts.app')

@section('title','Crear Cliente')

@section('content')
<div class="container">
    <h1 class="mb-4">Añadir Cliente</h1>

    @if(session('success'))
      <div class="alert alert-success mb-3">
        {{ session('success') }}
      </div>
    @endif

    @if($errors->any())
      <div class="alert alert-danger mb-3">
        @foreach($errors->all() as $error)
          <div>{{ $error }}</div>
        @endforeach
      </div>
    @endif

    <form action="{{ route('clients.store') }}" method="POST">
      @csrf

      <div class="mb-3">
        <label for="phone" class="form-label">Teléfono</label>
        <input 
          type="text"
          name="phone"
          id="phone"
          class="form-control"
          value="{{ old('phone') }}"
          required
          maxlength="20"
        >
      </div>

      {{-- Nuevo campo Telefono 2 --}}
      <div class="mb-3">
        <label for="phone_2" class="form-label">Teléfono secundario</label>
        <input 
          type="text"
          name="phone_2"
          id="phone_2"
          class="form-control"
          value="{{ old('phone_2') }}"
          maxlength="20"
          placeholder="Opcional"
        >
      </div>

      <div class="mb-3">
        <label for="name" class="form-label">Nombre</label>
        <input 
          type="text"
          name="name"
          id="name"
          class="form-control"
          value="{{ old('name') }}"
          required
          maxlength="100"
        >
      </div>

      <div class="mb-3">
        <label for="surname" class="form-label">Apellidos</label>
        <input 
          type="text"
          name="surname"
          id="surname"
          class="form-control"
          value="{{ old('surname') }}"
          required
          maxlength="100"
        >
      </div>

      <div class="mb-3">
        <label for="additional_info" class="form-label">Información Adicional</label>
        <textarea 
          name="additional_info"
          id="additional_info"
          class="form-control"
          rows="3"
        >{{ old('additional_info') }}</textarea>
      </div>

      <button type="submit" class="btn btn-primary w-100">Crear Cliente</button>
    </form>

    <!-- Botón Volver -->
    <button type="button" class="btn btn-secondary w-100 mt-2" onclick="history.back()">
      Volver
    </button>
</div>
@endsection
