{{-- resources/views/layouts/app.blade.php --}}
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>@yield('title', config('app.name', 'Mi Aplicación'))</title>

  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

  @yield('head')
</head>
<body class="bg-white text-dark">

  {{-- NAVBAR: sólo para usuarios autenticados --}}
  @auth
    @include('layouts.navbar')
  @endauth

  {{-- CONTENIDO PRINCIPAL --}}
  <main class="py-4">
    <div class="container mt-4">
      {{-- Mensaje de info genérico --}}
      @if(session('info'))
        <div class="alert alert-info mb-3">
          {{ session('info') }}
        </div>
      @endif

      {{-- Cada vista inyecta aquí su contenido --}}
      @yield('content')
    </div>
  </main>

  <!-- Bootstrap Bundle JS (incluye Popper para el navbar y modales) -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

  {{-- Scripts específicos de cada vista --}}
  @stack('scripts')
</body>
</html>
