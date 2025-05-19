<!doctype html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Etiqueta Depósito #{{ $deposit->id }}</title>
  <style>
    html, body {
      height: 100%;
      width: 100%;
      margin: 0;
      padding: 0;
      background: #fff;
      font-family: Arial, sans-serif;
    }
    body {
      /* Centrado solo visual, para el navegador */
      display: flex;
      align-items: flex-start;
      justify-content: flex-start;
      min-height: 100vh;
      min-width: 100vw;
    }
    .etiqueta {
      width: 62mm;
      height: 50mm;
      background: #fff;
      box-sizing: border-box;
      display: flex;
      flex-direction: column;
      justify-content: space-evenly;
      align-items: center;
      /* Sin bordes ni sombras */
      border: none;
      box-shadow: none;
      margin: 0;
      padding: 0;
      /* ¡Esto es crítico para evitar saltos de línea fuera de la etiqueta! */
      overflow: hidden;
    }
    .header {
      font-size: 10mm; /* Tamaño grande, ajusta si necesitas */
      font-weight: bold;
      color: #000;
      text-align: center;
      line-height: 1.1;
    }
    .line {
      font-size: 5mm;
      color: #333;
      text-align: center;
      line-height: 1.1;
    }
    .date {
      font-size: 4mm;
      color: #555;
      text-align: center;
    }
    @media print {
      html, body {
        width: 62mm;
        height: 50mm;
        margin: 0 !important;
        padding: 0 !important;
        background: #fff !important;
      }
      body {
        min-width: 62mm !important;
        min-height: 50mm !important;
        width: 62mm !important;
        height: 50mm !important;
        display: block !important;
      }
      .etiqueta {
        width: 62mm !important;
        height: 50mm !important;
        margin: 0 !important;
        padding: 0 !important;
        background: #fff !important;
        border: none !important;
        box-shadow: none !important;
        overflow: hidden !important;
        page-break-inside: avoid;
        page-break-after: avoid;
      }
      @page {
        size: 62mm 50mm;
        margin: 0;
      }
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
