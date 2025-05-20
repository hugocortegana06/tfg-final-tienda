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
      display: flex;
      align-items: flex-start;
      justify-content: flex-start;
      min-height: 100vh;
      min-width: 100vw;
    }
    .etiqueta {
      /* Mantenemos tamaño un poco reducido y ajustamos padding derecho */
      width: 60mm;
      height: 48mm;
      background: #fff;
      box-sizing: border-box;
      padding: 1mm 4mm 1mm 1mm;  /* top right bottom left */
      display: flex;
      flex-direction: column;
      justify-content: space-evenly;
      align-items: center;
      overflow: visible;
      /* Conservar un ligero escalado para más margen general */
      transform: scale(0.90);
      transform-origin: top center;
    }
    .header {
      font-size: 10mm;
      font-weight: bold;
      color: #000;
      text-align: center;
      line-height: 1.1;
      white-space: normal;
      overflow-wrap: break-word;
    }
    .line {
      font-size: 4mm;
      color: #333;
      text-align: center;
      line-height: 1.1;
      white-space: normal;
      overflow-wrap: break-word;
      hyphens: auto;
    }
    .date {
      font-size: 4mm;
      color: #555;
      text-align: center;
      white-space: nowrap;
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
        display: block !important;
      }
      .etiqueta {
        width: 60mm !important;
        height: 48mm !important;
        padding: 1mm 4mm 1mm 1mm !important;
        transform: scale(0.95) !important;
        overflow: visible !important;
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
    <div class="header">
      Depósito {{ $deposit->id }}
    </div>
    <div class="line">
      <strong>Cliente:</strong> {{ $deposit->client->name }} {{ $deposit->client->surname }}
    </div>
    <div class="line">
      <strong>Modelo:</strong> {{ $deposit->brand }} {{ $deposit->model }}
    </div>
    <div class="line">
      <strong>Problema:</strong> {{ $deposit->problem_description }}
    </div>
    <div class="date">
      Fecha: {{ now()->format('d/n/Y') }}
    </div>
  </div>

  <script>
    window.addEventListener('DOMContentLoaded', () => {
      function fitText(el, minSizePx = 4) {
        const parentWidth = el.clientWidth;
        let style = window.getComputedStyle(el);
        let fontSize = parseFloat(style.fontSize);
        while (el.scrollWidth > parentWidth && fontSize > minSizePx) {
          fontSize -= 0.5;
          el.style.fontSize = fontSize + 'px';
        }
      }
      document.querySelectorAll('.header, .line, .date').forEach(el => {
        fitText(el);
      });
    });
  </script>
</body>
</html>
