<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte de Cheques - CPS</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 9px; }
        h1 { color:#247D26; font-size: 14px; margin: 0 0 5px; }
        h2 { font-size: 11px; color:#1e3a8a; margin: 5px 0; }
        .header { text-align: center; margin-bottom: 10px; }
        .header p { margin: 2px 0; font-size: 8px; color:#666; }
        .filtros { font-size: 8px; color:#555; margin-bottom: 10px; }
        table { width: 100%; border-collapse: collapse; margin-top: 5px; }
        th { background: #1e3a8a; color: white; padding: 4px 3px; text-align: left; font-size: 8px; }
        td { padding: 3px; border-bottom: 1px solid #E5EBE5; }
        tr:nth-child(even) { background:#EDFFED; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .resumen { margin-top: 10px; }
        .resumen-item { display: inline-block; margin-right: 20px; font-size: 9px; }
        .resumen-label { color:#666; }
        .resumen-value { font-weight: bold; color:#1e3a8a; }
        .footer { text-align: center; font-size: 7px; color:#999; margin-top: 15px; border-top: 1px solid #ddd; padding-top: 5px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Sistema Integral de Pago CPS</h1>
        <p>Reporte de Cheques</p>
        <p>Generado el {{ now()->format('d/m/Y H:i') }}</p>
    </div>

    <div class="filtros">
        Período: {{ \Carbon\Carbon::parse($datos['filtros']['fecha_desde'])->format('d/m/Y') }}
        al {{ \Carbon\Carbon::parse($datos['filtros']['fecha_hasta'])->format('d/m/Y') }}
        @if(!empty($datos['filtros']['estado']))
            | Estado: {{ $datos['filtros']['estado'] }}
        @endif
    </div>

    <table>
        <thead>
            <tr>
                <th>N° Cheque</th>
                <th>Fecha Emisión</th>
                <th>Beneficiario</th>
                <th>Banco</th>
                <th>N° Cuenta</th>
                <th class="text-right">Monto</th>
                <th class="text-center">Estado</th>
            </tr>
        </thead>
        <tbody>
            @forelse($datos['cheques'] as $cheque)
            <tr>
                <td>{{ $cheque->numero_cheque }}</td>
                <td>{{ $cheque->fecha_emision?->format('d/m/Y') }}</td>
                <td>{{ $cheque->ordenPago?->beneficiario_nombre ?? '-' }}</td>
                <td>{{ $cheque->banco ?? '-' }}</td>
                <td>{{ $cheque->numero_cuenta ?? '-' }}</td>
                <td class="text-right">Bs {{ number_format($cheque->monto, 2) }}</td>
                <td class="text-center">{{ ucfirst($cheque->estado) }}</td>
            </tr>
            @empty
            <tr><td colspan="7" class="text-center" style="padding:15px;color:#999;">No se encontraron cheques en el período seleccionado</td></tr>
            @endforelse
        </tbody>
    </table>

    <div class="resumen">
        <h2>Resumen</h2>
        <span class="resumen-item"><span class="resumen-label">Total Cheques:</span> <span class="resumen-value">{{ $datos['resumen']['total_cheques'] }}</span></span>
        <span class="resumen-item"><span class="resumen-label">Monto Total:</span> <span class="resumen-value">Bs {{ number_format($datos['resumen']['monto_total'], 2) }}</span></span>
        @if(($datos['resumen']['monto_anulado'] ?? 0) > 0)
        <span class="resumen-item"><span class="resumen-label">Monto Anulado:</span> <span class="resumen-value">Bs {{ number_format($datos['resumen']['monto_anulado'], 2) }}</span></span>
        @endif
    </div>

    <div class="footer">
        <p>Sistema Integral de Pago CPS — Reporte de Cheques</p>
    </div>
</body>
</html>