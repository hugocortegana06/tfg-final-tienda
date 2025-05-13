<!doctype html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Factura Depósito #{{ $deposit->id }}</title>
  <style>
    /* Página A4 sin márgenes de impresora */
    @page {
      size: A4 portrait;
      margin: 0;
    }
    html, body {
      margin: 0;
      padding: 0;
      height: 100%;
      font-family: "Helvetica Neue", Arial, sans-serif;
      background: none;
      box-sizing: border-box;
    }
    /* Contenedor con margen interior de 20mm y altura ajustada */
    .container {
      margin: 20mm;
      height: calc(100% - 40mm);
      display: flex;
      flex-direction: column;
      justify-content: space-between;
      box-sizing: border-box;
    }
    /* Cada sección ocupa 1/3 exacto del alto disponible */
    .section {
      flex: 1;
      background: #ffffff;
      border: 1px solid #ddd;
      border-radius: 4px;
      padding: 12px;
      display: flex;
      flex-direction: column;
      justify-content: flex-start;
      box-sizing: border-box;
    }
    /* Espacio uniforme entre secciones */
    .section + .section {
      margin-top: 12px;
    }
    .section h2 {
      margin: 0 0 8px;
      font-size: 1.2em;
      font-weight: 600;
      color: #333;
      text-transform: uppercase;
      border-bottom: 1px solid #eee;
      padding-bottom: 4px;
    }
    .section p {
      margin: 4px 0;
      font-size: 1em;
      color: #555;
      line-height: 1.4;
    }
    
  </style>
</head>
<body>
  <div class="container">
    <!-- Datos Personales -->
    <div class="section">
      <h2>Datos Personales</h2>
      <p><strong>Nombre:</strong> {{ $deposit->client->name }} {{ $deposit->client->surname }}</p>
      <p><strong>Teléfono:</strong> {{ $deposit->client->phone }}</p>
      <p><strong>Teléfono alternativo:</strong> {{ $deposit->client->phone_2 }}</p>

    </div>

    <!-- Datos Dispositivo -->
    <div class="section">
      <h2>Datos Dispositivo</h2>
      <p><strong>Marca / Modelo:</strong> {{ $deposit->brand }} {{ $deposit->model }}</p>
      <p><strong>N.º Serie:</strong> {{ $deposit->serial_number }}</p>
      <p><strong>Fecha Entrada:</strong> {{ $deposit->date_in }}</p>
      @if($deposit->date_out)
        <p><strong>Fecha Salida:</strong> {{ $deposit->date_out }}</p>
      @endif
    </div>

    <!-- Descripción Problema -->
    <div class="section">
      <h2>Descripción del Problema</h2>
      <p>{{ $deposit->problem_description }}</p>
      @if($deposit->more_info)
        <p><em>Info adicional:</em> {{ $deposit->more_info }}</p>
      @endif
    </div>
  </div>
</body>
</html>
