<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte de Beneficiarios - CPS</title>
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
        <p>Reporte de Beneficiarios</p>
        <p>Generado el {{ now()->format('d/m/Y H:i') }}</p>
    </div>

    <div class="filtros">
        @if(!empty($datos['filtros']['tipo_documento']))
            Tipo Documento: {{ $datos['filtros']['tipo_documento'] }}
        @else
            Todos los beneficiarios
        @endif
    </div>

    <table>
        <thead>
            <tr>
                <th>N°</th>
                <th>Nombre / Razón Social</th>
                <th>Apellidos</th>
                <th>CI / NIT</th>
                <th class="text-right">Órdenes</th>
                <th class="text-right">Monto Total</th>
            </tr>
        </thead>
        <tbody>
            @forelse($datos['beneficiarios'] as $i => $beneficiario)
            <tr>
                <td>{{ $i + 1 }}</td>
                <td>{{ $beneficiario->nombre_razon_social }}</td>
                <td>{{ $beneficiario->apellidos ?? '-' }}</td>
                <td>{{ $beneficiario->ci_nit ?? '-' }}</td>
                <td class="text-right">{{ $beneficiario->ordenes_pago_count }}</td>
                <td class="text-right">Bs {{ number_format($beneficiario->ordenes_pago_sum_monto_total ?? 0, 2) }}</td>
            </tr>
            @empty
            <tr><td colspan="6" class="text-center" style="padding:15px;color:#999;">No se encontraron beneficiarios</td></tr>
            @endforelse
        </tbody>
    </table>

    <div class="resumen">
        <h2>Resumen</h2>
        <span class="resumen-item"><span class="resumen-label">Total Beneficiarios:</span> <span class="resumen-value">{{ $datos['resumen']['total_beneficiarios'] }}</span></span>
        <span class="resumen-item"><span class="resumen-label">Monto Total Histórico:</span> <span class="resumen-value">Bs {{ number_format($datos['resumen']['monto_total_historico'], 2) }}</span></span>
    </div>

    <div class="footer">
        <p>Sistema Integral de Pago CPS — Reporte de Beneficiarios</p>
    </div>
</body>
</html>