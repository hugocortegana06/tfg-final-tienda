<nav class="navbar navbar-expand navbar-light bg-light shadow-sm mb-4">
    <div class="container-fluid">
        <a class="navbar-brand" href="{{ route('dashboard') }}">
            MiAplicación
        </a>

        <div class="ms-auto">
            {{-- Botón Cerrar Sesión --}}
            <form action="{{ route('logout') }}" method="POST" class="d-inline">
                @csrf
                <button type="submit" class="btn btn-outline-danger">
                    Cerrar sesión
                </button>
            </form>
        </div>
    </div>
</nav>
