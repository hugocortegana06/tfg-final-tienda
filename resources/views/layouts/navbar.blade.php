{{-- resources/views/layouts/navbar.blade.php --}}
<nav class="navbar navbar-expand-lg navbar-light bg-white border-bottom">
  <div class="container">
    <a class="navbar-brand d-flex align-items-center" href="{{ route('dashboard') }}">
      {{-- Logo --}}
      <img src="{{ asset('images/logo.png') }}" 
           alt="{{ config('app.name') }}" 
           height="40" 
           class="me-2">
      {{-- Opcionalmente, un nombre al lado --}}
      {{-- <span class="fw-bold">{{ config('app.name') }}</span> --}}
    </a>

    <button class="navbar-toggler" type="button" data-bs-toggle="collapse"
            data-bs-target="#navbarSupportedContent">
      <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse" id="navbarSupportedContent">
      <ul class="navbar-nav ms-auto align-items-center">
        {{-- Saludo y rol --}}
        <li class="nav-item">
          <span class="nav-link">Hola, {{ Auth::user()->name }}
            ({{ Auth::user()->role }})</span>
        </li>
        {{-- Histórico de entregas --}}
        <li class="nav-item">
          <form method="GET" action="{{ route('deposits.entregados') }}">
            <button class="btn btn-link nav-link" type="submit">
              Histórico de entregas
            </button>
          </form>
        </li>
        {{-- Cerrar sesión --}}
        <li class="nav-item">
          <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button class="btn btn-link nav-link" type="submit">
              Cerrar sesión
            </button>
          </form>
        </li>
      </ul>
    </div>
  </div>
</nav>
