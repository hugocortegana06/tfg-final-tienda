<!doctype html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Etiqueta Depósito #{{ $deposit->id }}</title>
  <style>
    body {
      margin: 0;
      padding: 0;
      font-family: "Arial", sans-serif;
      background: #f0f0f0;
      display: flex;
      align-items: center;
      justify-content: center;
      height: 100vh;
    }
    .etiqueta {
      width: 260px;
      height: 160px;
      background: #ffffff;
      border: 2px solid #007bff;
      border-radius: 6px;
      box-shadow: 0 2px 4px rgba(0,0,0,0.1);
      padding: 12px 8px;
      box-sizing: border-box;
      display: flex;
      flex-direction: column;
      justify-content: space-around;
      align-items: center;
    }
    .header {
      font-size: 1.4em;
      font-weight: bold;
      color: #007bff;
      text-align: center;
      margin: 8px 0 4px; /* espacio extra arriba */
    }
    .line {
      font-size: 1em;
      color: #333;
      text-align: center;
      line-height: 1.2;
      margin: 0;
      padding: 0 8px;
    }
    .date {
      font-size: 0.8em;
      color: #555;
      text-align: center;
      margin: 4px 0 8px; /* espacio abajo */
    }
  </style>
</head>
<body>
  <div class="etiqueta">
    <div class="header">Depósito {{ $deposit->id }}</div>
    <div class="line"><strong>Cliente:</strong> {{ $deposit->client->name }} {{ $deposit->client->surname }}</div>
    <div class="line"><strong>Modelo:</strong> {{ $deposit->brand }} {{ $deposit->model }}</div>
    <div class="date">Fecha: {{ now()->format('d/n/Y') }}</div>
  </div>
</body>
</html>