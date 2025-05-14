<!doctype html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Factura Depósito #{{ $deposit->id }}</title>
  <style>
    @page { size: A4 portrait; margin: 0; }
    html, body {
      margin: 0; padding: 0; height:100%;
      font-family: "Helvetica Neue", Arial, sans-serif;
      box-sizing: border-box; position: relative;
    }

    /* ---------------------------------------------------
       Logo centrado y más grande
    --------------------------------------------------- */
    .logo {
      position: absolute;
      top: 10mm;                /* separa del borde superior */
      left: 50%;                /* point of centering */
      transform: translateX(-50%); /* centra horizontal */
      z-index: 10;
      text-align: center;
    }
    .logo img {
      height: 18mm;             /* un “pelín” más grande */
      display: block;
    }

    /* Empujamos el contenido hacia abajo dejando hueco al logo */
    .container {
      /* 10mm (top margin) + 18mm (logo) + 5mm separación = 33mm */
      margin: 33mm 20mm 20mm 20mm;
      height: calc(100% - 33mm - 20mm);
      display: flex;
      flex-direction: column;
      justify-content: space-between;
      box-sizing: border-box;
    }

    .section {
      flex: 1;
      background: #fff;
      border: 1px solid #ddd;
      border-radius: 4px;
      padding: 12px;
      display: flex;
      flex-direction: column;
      justify-content: flex-start;
      box-sizing: border-box;
    }
    .section + .section { margin-top: 12px; }
    .section h2 {
      margin: 0 0 8px;
      font-size: 1.2em; font-weight: 600; color: #333;
      text-transform: uppercase;
      border-bottom: 1px solid #eee;
      padding-bottom: 4px;
    }
    .section p {
      margin: 4px 0; font-size: 1em; color: #555; line-height: 1.4;
    }
  </style>
</head>
<body>
  {{-- Logo centrado --}}
  <div class="logo">
    <img src="file://{{ public_path('images/logo_bn.jpg') }}" alt="Logo">
  </div>

  <div class="container">
    <div class="section">
      <h2>Datos Personales</h2>
      <p><strong>Nombre:</strong> {{ $deposit->client->name }} {{ $deposit->client->surname }}</p>
      <p><strong>Teléfono:</strong> {{ $deposit->client->phone }}</p>
      @if(!empty($deposit->client->phone_2))
        <p><strong>Teléfono alternativo:</strong> {{ $deposit->client->phone_2 }}</p>
      @endif
    </div>

    <div class="section">
      <h2>Datos Dispositivo</h2>
      <p><strong>Marca / Modelo:</strong> {{ $deposit->brand }} {{ $deposit->model }}</p>
      <p><strong>N.º Serie:</strong> {{ $deposit->serial_number }}</p>
      <p><strong>Fecha Entrada:</strong> {{ $deposit->date_in }}</p>
      @if($deposit->date_out)
        <p><strong>Fecha Salida:</strong> {{ $deposit->date_out }}</p>
      @endif
    </div>

    <div class="section">
      <h2>Descripción del Problema</h2>
      @if($deposit->more_info)
        <p><em>Info adicional:</em> {{ $deposit->more_info }}</p>
      @endif
    </div>


    <div class="section">
      <h2>Presupuesto</h2>
      
      @if($deposit->more_info)
        <p><em>Presupuesto:</em> {{ $deposit->budget }} €</p>
      @endif
    </div>

  </div>
</body>
</html>
