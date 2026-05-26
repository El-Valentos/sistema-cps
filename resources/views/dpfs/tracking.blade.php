<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte de Tracking</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 9px; color: #333; }
        .header { text-align: center; margin-bottom: 20px; border-bottom: 2px solid #333; padding-bottom: 10px; }
        .header h1 { font-size: 16px; margin: 0; }
        .header p { font-size: 10px; color: #666; margin: 2px 0; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th { background-color: #1a5632; color: #fff; padding: 6px 4px; text-align: left; font-size: 8px; text-transform: uppercase; }
        td { padding: 5px 4px; border-bottom: 1px solid #ddd; }
        tr:nth-child(even) { background-color: #f9f9f9; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .font-mono { font-family: 'Courier New', monospace; }
        .fecha { font-size: 8px; color: #999; margin-top: 15px; text-align: center; }
        .filtros { font-size: 8px; color: #555; margin-bottom: 10px; }
        .estado-badge { display: inline-block; padding: 1px 6px; border-radius: 3px; font-size: 7px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>REPORTE DE SEGUIMIENTO DE ÓRDENES</h1>
        <p>Sistema Integral de Pago CPS</p>
        <p>Generado: {{ now()->format('d/m/Y H:i') }}</p>
    </div>

    @if(request('numero_orden') || request('estado'))
    <div class="filtros">
        <strong>Filtros aplicados:</strong>
        @if(request('numero_orden')) | N° Orden: {{ request('numero_orden') }} @endif
        @if(request('estado')) | Estado: {{ request('estado') }} @endif
    </div>
    @endif

    <table>
        <thead>
            <tr>
                <th>N° Orden</th>
                <th>Beneficiario</th>
                <th class="text-right">Monto</th>
                <th>Estado Actual</th>
                <th>Último Movimiento</th>
            </tr>
        </thead>
        <tbody>
            @forelse($ordenes as $op)
            <tr>
                <td class="font-mono">{{ $op->numero_orden }}</td>
                <td>{{ $op->beneficiario_nombre ?? '-' }}</td>
                <td class="text-right">Bs. {{ number_format($op->neto_pagar, 2) }}</td>
                <td>
                    <span class="estado-badge">{{ $op->estado_label }}</span>
                </td>
                <td>
                    @if($op->trackingHistorial->first())
                        {{ $op->trackingHistorial->first()->accion }} — {{ \Carbon\Carbon::parse($op->trackingHistorial->first()->fecha_hora)->format('d/m/Y H:i') }}
                    @else
                        —
                    @endif
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="5" class="text-center" style="padding: 30px; color: #999;">No hay órdenes para mostrar</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <div class="fecha">
        Total de registros: {{ $ordenes->count() }} | {{ config('app.ciudad', 'Cochabamba') }}, {{ now()->format('d/m/Y') }}
    </div>
</body>
</html>
