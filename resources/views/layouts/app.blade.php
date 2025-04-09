<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>@yield('title','Mi Aplicación')</title>

    {{-- Incluye tu CSS (Bootstrap, Tailwind, etc.) --}}
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body class="bg-white text-dark">

    @auth
        {{-- Incluimos el navbar sólo si está autenticado --}}
        @include('layouts.navbar')
    @endauth

    <div class="container mt-4">
        {{-- Mensajes flash (success / info) --}}
        @if(session('success'))
            <div class="alert alert-success mb-3">
                {{ session('success') }}
            </div>
        @endif
        @if(session('info'))
            <div class="alert alert-info mb-3">
                {{ session('info') }}
            </div>
        @endif

        {{-- Contenido de cada vista concreta --}}
        @yield('content')
    </div>

    {{-- Scripts --}}
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
