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
        <div id="infoMessage" class="alert alert-info mb-3">
          {{ session('info') }}
        </div>
      @endif

      {{-- Cada vista inyecta aquí su contenido --}}
      @yield('content')
    </div>
  </main>

  <!-- Bootstrap Bundle JS (incluye Popper para el navbar y modales) -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

  <!-- Script para ocultar el mensaje de sesión tras 3 segundos -->
  <script>
    document.addEventListener('DOMContentLoaded', function() {
      const info = document.getElementById('infoMessage');
      if (info) {
        setTimeout(() => {
          // Bootstrap usa las clases .fade y .show para transiciones
          info.classList.add('fade');
          info.classList.remove('show');
          // Después de la transición (150ms aprox), eliminamos el nodo
          setTimeout(() => info.remove(), 150);
        }, 3000);
      }
    });
  </script>

  {{-- Scripts específicos de cada vista --}}
  @stack('scripts')
</body>
</html>
