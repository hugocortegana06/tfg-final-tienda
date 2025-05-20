@extends('layouts.app')

@section('title', 'Nuevo Depósito')

@section('head')
  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/pattern-lock-js@1.0.1/dist/patternlock.css" rel="stylesheet">
  <style>
    #clientList {
      position: absolute;
      z-index: 1000;
      width: 100%;
      max-height: 200px;
      overflow-y: auto;
    }
  </style>
@endsection

@section('content')
<div class="container">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="mb-0">Añadir Depósito</h1>
    <a href="{{ route('clients.create') }}" class="btn btn-secondary">Añadir cliente</a>
  </div>

  {{-- Mensaje de éxito --}}
  @if(session('success'))
    <div id="serverSuccess" class="alert alert-success mb-3">
      {{ session('success') }}
    </div>
  @endif




  <form action="{{ route('deposits.store') }}" method="POST" id="depositForm">
    @csrf

    {{-- Buscador de Cliente --}}
    <div class="mb-3 position-relative">
      <label for="client_search" class="form-label">Cliente</label>
      <input type="text" id="client_search" class="form-control" placeholder="Escribe nombre o apellidos..." autocomplete="off" >
      @error('client_search')
        <small class="text-danger">{{ $message }}</small>
      @enderror

      <input type="hidden" name="client_phone" id="client_phone" value="{{ old('client_phone') }}" >
      @error('client_phone')
        <small class="text-danger">{{ $message }}</small>
      @enderror

      <ul class="list-group" id="clientList" style="display:none;"></ul>
    </div>

    <div class="row">
      <div class="col-md-4 mb-3">
        <label for="brand" class="form-label">Marca</label>
        <input name="brand" id="brand" class="form-control" value="{{ old('brand') }}" >
        @error('brand')
          <small class="text-danger">{{ $message }}</small>
        @enderror
      </div>
      <div class="col-md-4 mb-3">
        <label for="model" class="form-label">Modelo</label>
        <input name="model" id="model" class="form-control" value="{{ old('model') }}" >
        @error('model')
          <small class="text-danger">{{ $message }}</small>
        @enderror
      </div>
      <div class="col-md-4 mb-3">
        <label for="serial_number" class="form-label">N.º Serie</label>
        <input name="serial_number" id="serial_number" class="form-control" value="{{ old('serial_number') }}" >
        @error('serial_number')
          <small class="text-danger">{{ $message }}</small>
        @enderror
      </div>
    </div>

    <div class="mb-3">
      <label for="problem_description" class="form-label">Descripción del Problema</label>
      <textarea name="problem_description" id="problem_description" class="form-control" rows="3" >{{ old('problem_description') }}</textarea>
      @error('problem_description')
        <small class="text-danger">{{ $message }}</small>
      @enderror
    </div>

    <div class="mb-3">
      <label for="more_info" class="form-label">Información Adicional</label>
      <textarea name="more_info" id="more_info" class="form-control" rows="2">{{ old('more_info') }}</textarea>
      @error('more_info')
        <small class="text-danger">{{ $message }}</small>
      @enderror
    </div>

    <div class="mb-3">
      <label for="pin_or_password" class="form-label">PIN o Contraseña (opcional)</label>
      <input type="text" name="pin_or_password" id="pin_or_password" class="form-control" value="{{ old('pin_or_password') }}">
      @error('pin_or_password')
        <small class="text-danger">{{ $message }}</small>
      @enderror
    </div>

    <div class="row mb-3">
      <div class="col-md-6">
        <label for="budget" class="form-label">Presupuesto (€)</label>
        <input type="number" name="budget" id="budget" class="form-control" step="0.01" min="0" value="{{ old('budget') }}">
        <small class="form-text text-muted">Introduce el presupuesto en euros (opcional).</small>
        @error('budget')
          <small class="text-danger">{{ $message }}</small>
        @enderror
      </div>
      <div class="col-md-6 d-flex align-items-center">
        <div class="form-check mt-4">
          <input class="form-check-input" type="checkbox" name="under_warranty" id="under_warranty" value="1" {{ old('under_warranty') ? 'checked' : '' }}>
          <label class="form-check-label" for="under_warranty">En garantía</label>
        </div>
        @error('under_warranty')
          <small class="text-danger">{{ $message }}</small>
        @enderror
      </div>
    </div>

    <div class="mb-3">
      <label for="status" class="form-label">Estado</label>
      <select name="status" id="status" class="form-select" >
        <option value="">-- Selecciona Estado --</option>
        @foreach(['En curso','Electrónico','Finalizado','Entregado'] as $st)
          <option value="{{ $st }}" {{ old('status')===$st?'selected':'' }}>
            {{ $st }}
          </option>
        @endforeach
      </select>
      @error('status')
        <small class="text-danger">{{ $message }}</small>
      @enderror
    </div>

    <div class="mb-4 text-center">
      <label class="form-label d-block">Patrón de desbloqueo (opcional)</label>
      <svg id="lock" class="patternlock" viewBox="0 0 100 100"
           xmlns="http://www.w3.org/2000/svg"
           style="width:200px;height:200px;margin:auto;display:block; border:1px solid #ccc;border-radius:8px;">
        <g class="lock-actives"></g>
        <g class="lock-lines"></g>
        <g class="lock-dots">
          <circle cx="20" cy="20" r="5"/>
          <circle cx="50" cy="20" r="5"/>
          <circle cx="80" cy="20" r="5"/>
          <circle cx="20" cy="50" r="5"/>
          <circle cx="50" cy="50" r="5"/>
          <circle cx="80" cy="50" r="5"/>
          <circle cx="20" cy="80" r="5"/>
          <circle cx="50" cy="80" r="5"/>
          <circle cx="80" cy="80" r="5"/>
        </g>
      </svg>
      <input type="hidden" name="unlock_password" id="unlock_password" value="{{ old('unlock_password') }}">
      @error('unlock_password')
        <small class="text-danger">{{ $message }}</small>
      @enderror
    </div>

    <button type="submit" class="btn btn-primary w-100">Guardar Depósito</button>
    <button type="button" class="btn btn-secondary w-100 mt-2" onclick="history.back()">Volver</button>
  </form>
</div>
@endsection

@push('scripts')
  <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/pattern-lock-js@1.0.1/dist/patternlock.js"></script>
  <script>
  $(function(){
    // 1) Autocomplete de clientes
    const $input   = $('#client_search'),
          $list    = $('#clientList'),
          $hidden  = $('#client_phone');

    $input.on('input', function(){
      const q = $(this).val().trim();
      if(q.length < 2) { $list.hide(); return; }
      $.getJSON("{{ route('clients.search') }}", { query: q }, function(data){
        $list.empty();
        if(!data.length) {
          $list.hide();
          return;
        }
        data.forEach(c => {
          $('<li>')
            .addClass('list-group-item list-group-item-action')
            .text(`${c.name} ${c.surname} (${c.phone})`)
            .attr('data-phone', c.phone)
            .appendTo($list);
        });
        $list.show();
      });
    });

    $list.on('click','li', function(){
      const name  = $(this).text(),
            phone = $(this).data('phone');
      $input.val(name);
      $hidden.val(phone);
      $list.hide();
    });

    $(document).on('click', e => {
      if(!$(e.target).closest('#client_search, #clientList').length) {
        $list.hide();
      }
    });

    setTimeout(() => $('#serverSuccess').fadeOut(), 3000);
    new PatternLock("#lock", {
      vibrate: false,
      onPattern: pa => {
        let raw = Array.isArray(pa) ? pa.join('') : pa;
        $('#unlock_password').val(raw);
      }
    });
  });
  </script>
@endpush
