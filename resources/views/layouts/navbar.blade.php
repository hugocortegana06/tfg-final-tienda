{{-- resources/views/layouts/navbar.blade.php --}}
<nav class="navbar navbar-expand-lg navbar-light bg-white border-bottom">
  <div class="container">
    <a class="navbar-brand" href="{{ route('dashboard') }}">
      {{-- Aquí tu logo o nombre de la app --}}
      {{ config('app.name', 'Mi Aplicación') }}
    </a>

    <div class="collapse navbar-collapse">
      <ul class="navbar-nav ms-auto">
        {{-- Mostrar rol/nombre --}}
        <li class="nav-item">
          <span class="nav-link">Hola, {{ Auth::user()->name }} ({{ Auth::user()->role }})</span>
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
            <button class="btn btn-link nav-link" type="submit">Cerrar sesión</button>
          </form>
        </li>
      </ul>
    </div>
  </div>
</nav>
