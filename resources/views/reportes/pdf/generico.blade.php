<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte - CPS</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 10px; text-align: center; padding-top: 40px; }
        h1 { color:#247D26; font-size: 16px; }
        .mensaje { color:#666; font-size: 12px; margin-top: 20px; }
        .detalle { color:#999; font-size: 10px; margin-top: 10px; }
    </style>
</head>
<body>
    <h1>Sistema Integral de Pago CPS</h1>
    <p>Reporte generado el {{ now()->format('d/m/Y H:i') }}</p>
    <p class="mensaje">El tipo de reporte "<strong>{{ $tipo ?? 'N/A' }}</strong>" no está disponible actualmente.</p>
    <p class="detalle">Seleccione otro tipo de reporte desde el generador.</p>
</body>
</html>
