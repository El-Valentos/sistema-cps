<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte de Envío</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 10px; margin: 0; padding: 15px; color: #000; }
        .header { border-bottom: 2px solid #1e3a8a; padding-bottom: 10px; margin-bottom: 15px; display: flex; justify-content: space-between; align-items: center; }
        .logo { font-size: 18px; font-weight: bold; color: #1e3a8a; }
        .meta { font-size: 9px; color: #4b5563; text-align: right; }
        .title { font-size: 14px; font-weight: bold; text-align: center; margin-bottom: 15px; color: #1e3a8a; }
        .info { background: #f3f4f6; padding: 8px 12px; border-radius: 4px; margin-bottom: 15px; font-size: 10px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 15px; }
        th { background: #1e3a8a; color: #fff; padding: 6px 8px; text-align: left; font-size: 9px; text-transform: uppercase; }
        td { padding: 5px 8px; border-bottom: 1px solid #e5e7eb; }
        tr:nth-child(even) td { background: #f9fafb; }
        .total { background: #e5e7eb !important; font-weight: bold; }
        .resumen { border-top: 2px solid #1e3a8a; padding-top: 10px; display: flex; justify-content: space-between; font-size: 11px; }
        .footer { text-align: center; font-size: 8px; color: #9ca3af; margin-top: 20px; border-top: 1px solid #e5e7eb; padding-top: 5px; }
    </style>
</head>
<body>
    <div class="header">
        <div class="logo">{{ config('app.name') }}</div>
        <div class="meta">
            {{ config('app.ciudad') }}, {{ $fecha }}<br>
            Usuario: {{ $usuario }}
        </div>
    </div>

    <div class="title">REPORTE DE ENVÍO</div>

    <div class="info">
        <strong>De:</strong> {{ $origen }} &nbsp;&nbsp;|&nbsp;&nbsp;
        <strong>Para:</strong> {{ $destino }} &nbsp;&nbsp;|&nbsp;&nbsp;
        <strong>Items:</strong> {{ $totalItems }} &nbsp;&nbsp;|&nbsp;&nbsp;
        <strong>Monto Total:</strong> Bs. {{ number_format($totalMonto, 2) }}
    </div>

    <table>
        <thead>
            <tr>
                @if($tipo === 'cheque')
                    <th>N° Cheque</th>
                @endif
                <th>N° Orden</th>
                <th>Beneficiario</th>
                <th style="text-align: right;">Monto</th>
            </tr>
        </thead>
        <tbody>
            @foreach($items as $item)
            @php $orden = $item instanceof \App\Models\Cheque ? $item->ordenPago : $item; @endphp
            <tr>
                @if($tipo === 'cheque')
                    <td>{{ $item->numero_cheque ?? 'Pendiente' }}</td>
                @endif
                <td>{{ $orden->numero_orden }}</td>
                <td>{{ $orden->beneficiario_nombre }} {{ $orden->beneficiario_apellidos ?? '' }}</td>
                <td style="text-align: right;">Bs. {{ number_format($item instanceof \App\Models\Cheque ? $item->monto : $item->neto_pagar, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="resumen">
        <span><strong>Total Items:</strong> {{ $totalItems }}</span>
        <span><strong>Monto Total:</strong> Bs. {{ number_format($totalMonto, 2) }}</span>
    </div>

    <div class="footer">
        Generado por Sistema CPS — {{ $fecha }}
    </div>
</body>
</html>
