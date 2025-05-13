{{-- resources/views/deposits/label.blade.php --}}
<!doctype html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Etiqueta Depósito #{{ $deposit->id }}</title>
  <style>
    body {
      margin: 0;
      padding: 0;
      font-family: Arial, sans-serif;
      background: #f0f0f0;
      display: flex;
      align-items: center;
      justify-content: center;
      height: 100vh;
    }

    /* Rectángulo negro externo */
    .black-frame {
      background: #000;
      padding: 6px;
      border-radius: 8px;
      display: inline-flex;
    }

    /* Etiqueta blanca interna */
    .etiqueta {
      width: 260px;
      height: 160px;
      background: #fff;
      border-radius: 4px;
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
      color: #000;
      text-align: center;
      margin: 8px 0 4px;
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
      margin: 4px 0 8px;
    }
  </style>
</head>
<body>
  <div class="black-frame">
    <div class="etiqueta">
      <div class="header">Depósito {{ $deposit->id }}</div>
      <div class="line"><strong>Cliente:</strong> {{ $deposit->client->name }} {{ $deposit->client->surname }}</div>
      <div class="line"><strong>Modelo:</strong> {{ $deposit->brand }} {{ $deposit->model }}</div>
      <div class="date">Fecha: {{ now()->format('d/n/Y') }}</div>
    </div>
  </div>
</body>
</html>
