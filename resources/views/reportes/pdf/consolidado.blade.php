<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte Consolidado - CPS</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 8px; color: #333; }
        h1 { color: #247D26; font-size: 14px; margin: 0 0 5px; }
        h2 { font-size: 11px; color: #1e3a8a; margin: 15px 0 5px; border-bottom: 1px solid #ccc; padding-bottom: 3px; }
        h3 { font-size: 9px; color: #555; margin: 10px 0 3px; }
        .header { text-align: center; margin-bottom: 10px; }
        .header p { margin: 2px 0; font-size: 8px; color: #666; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 8px; font-size: 7px; }
        th { background: #1e3a8a; color: white; padding: 4px 3px; text-align: left; font-size: 7px; }
        td { padding: 3px; border-bottom: 1px solid #E5EBE5; }
        tr:nth-child(even) { background: #EDFFED; }
        .kpi-grid { display: flex; flex-wrap: wrap; gap: 4px; margin-bottom: 8px; }
        .kpi { border: 1px solid #ddd; padding: 4px 6px; border-radius: 3px; flex: 1; min-width: 80px; }
        .kpi-label { font-size: 6px; color: #666; text-transform: uppercase; }
        .kpi-value { font-size: 10px; font-weight: bold; color: #1e3a8a; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .badge { display: inline-block; padding: 1px 4px; border-radius: 3px; font-size: 6px; background: #eee; }
        .footer { text-align: center; font-size: 7px; color: #999; margin-top: 15px; border-top: 1px solid #ddd; padding-top: 5px; }
        .section { margin-bottom: 12px; page-break-inside: avoid; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Sistema Integral de Pago CPS</h1>
        <p>Reporte Consolidado por Áreas</p>
        <p>Generado el {{ $reportes['generado_en']->format('d/m/Y H:i') }}</p>
    </div>

    {{-- TESORERÍA --}}
    <div class="section">
        <h2>1. Tesorería</h2>
        <div class="kpi-grid">
            <div class="kpi"><div class="kpi-label">Pendientes</div><div class="kpi-value">{{ $reportes['tesoreria']['resumen']['pendientes'] }}</div></div>
            <div class="kpi"><div class="kpi-label">Rechazados</div><div class="kpi-value">{{ $reportes['tesoreria']['resumen']['rechazados'] }}</div></div>
            <div class="kpi"><div class="kpi-label">En Flujo</div><div class="kpi-value">{{ $reportes['tesoreria']['resumen']['en_flujo'] }}</div></div>
            <div class="kpi"><div class="kpi-label">Monto del Mes</div><div class="kpi-value">Bs {{ number_format($reportes['tesoreria']['resumen']['monto_mes'], 2) }}</div></div>
        </div>
        <h3>Órdenes Pendientes / Rechazadas</h3>
        <table>
            <thead>
                <tr><th>N° Orden</th><th>Fecha</th><th>Beneficiario</th><th>Concepto</th><th class="text-right">Monto</th><th class="text-center">Estado</th></tr>
            </thead>
            <tbody>
                @forelse($reportes['tesoreria']['ordenes_pendientes'] as $o)
                <tr>
                    <td>{{ $o->numero_orden }}</td>
                    <td>{{ $o->fecha_orden?->format('d/m/Y') }}</td>
                    <td>{{ $o->beneficiario_nombre }}</td>
                    <td>{{ $o->concepto }}</td>
                    <td class="text-right">Bs {{ number_format($o->monto_total, 2) }}</td>
                    <td class="text-center"><span class="badge">{{ $o->estado_label }}</span></td>
                </tr>
                @empty
                <tr><td colspan="6" class="text-center">Sin registros</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- FINANCIERA --}}
    <div class="section">
        <h2>2. Financiera</h2>
        <div class="kpi-grid">
            <div class="kpi"><div class="kpi-label">Órdenes Pendientes</div><div class="kpi-value">{{ $reportes['financiera']['resumen']['ordenes_pendientes'] }}</div></div>
            <div class="kpi"><div class="kpi-label">Aprobadas (Mes)</div><div class="kpi-value">{{ $reportes['financiera']['resumen']['ordenes_aprobadas'] }}</div></div>
            <div class="kpi"><div class="kpi-label">Rechazadas (Mes)</div><div class="kpi-value">{{ $reportes['financiera']['resumen']['ordenes_rechazadas'] }}</div></div>
            <div class="kpi"><div class="kpi-label">Cheques Pendientes</div><div class="kpi-value">{{ $reportes['financiera']['resumen']['cheques_pendientes'] }}</div></div>
            <div class="kpi"><div class="kpi-label">Monto Pend. Órdenes</div><div class="kpi-value">Bs {{ number_format($reportes['financiera']['resumen']['monto_ordenes_pendientes'], 2) }}</div></div>
        </div>
        <h3>Órdenes Pendientes de Aprobación</h3>
        <table>
            <thead><tr><th>N° Orden</th><th>Beneficiario</th><th class="text-right">Monto</th></tr></thead>
            <tbody>
                @forelse($reportes['financiera']['ordenes_pendientes'] as $o)
                <tr><td>{{ $o->numero_orden }}</td><td>{{ $o->beneficiario_nombre }}</td><td class="text-right">Bs {{ number_format($o->monto_total, 2) }}</td></tr>
                @empty
                <tr><td colspan="3" class="text-center">Sin registros</td></tr>
                @endforelse
            </tbody>
        </table>
        <h3>Cheques Pendientes de Aprobación</h3>
        <table>
            <thead><tr><th>N° Cheque</th><th>Beneficiario</th><th class="text-right">Monto</th></tr></thead>
            <tbody>
                @forelse($reportes['financiera']['cheques_pendientes'] as $c)
                <tr><td>{{ $c->numero_cheque }}</td><td>{{ $c->ordenPago?->beneficiario_nombre ?? '-' }}</td><td class="text-right">Bs {{ number_format($c->monto, 2) }}</td></tr>
                @empty
                <tr><td colspan="3" class="text-center">Sin registros</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- CONTABILIDAD --}}
    <div class="section">
        <h2>3. Contabilidad</h2>
        <div class="kpi-grid">
            <div class="kpi"><div class="kpi-label">Órdenes Pendientes</div><div class="kpi-value">{{ $reportes['contabilidad']['resumen']['ordenes_pendientes'] }}</div></div>
            <div class="kpi"><div class="kpi-label">Cheques Emitidos (Mes)</div><div class="kpi-value">{{ $reportes['contabilidad']['resumen']['cheques_emitidos'] }}</div></div>
            <div class="kpi"><div class="kpi-label">Enviados Presupuesto</div><div class="kpi-value">{{ $reportes['contabilidad']['resumen']['cheques_enviados_presupuesto'] }}</div></div>
            <div class="kpi"><div class="kpi-label">Anulados (Mes)</div><div class="kpi-value">{{ $reportes['contabilidad']['resumen']['cheques_anulados_mes'] }}</div></div>
            <div class="kpi"><div class="kpi-label">Monto Emitido (Mes)</div><div class="kpi-value">Bs {{ number_format($reportes['contabilidad']['resumen']['monto_cheques_mes'], 2) }}</div></div>
        </div>
        <h3>Órdenes Pendientes</h3>
        <table>
            <thead><tr><th>N° Orden</th><th>Beneficiario</th><th class="text-right">Monto</th></tr></thead>
            <tbody>
                @forelse($reportes['contabilidad']['ordenes_pendientes'] as $o)
                <tr><td>{{ $o->numero_orden }}</td><td>{{ $o->beneficiario_nombre }}</td><td class="text-right">Bs {{ number_format($o->monto_total, 2) }}</td></tr>
                @empty
                <tr><td colspan="3" class="text-center">Sin registros</td></tr>
                @endforelse
            </tbody>
        </table>
        <h3>Últimos Cheques Emitidos</h3>
        <table>
            <thead><tr><th>N° Cheque</th><th>Beneficiario</th><th class="text-right">Monto</th><th class="text-center">Estado</th></tr></thead>
            <tbody>
                @forelse($reportes['contabilidad']['ultimos_cheques'] as $c)
                <tr><td>{{ $c->numero_cheque }}</td><td>{{ $c->ordenPago?->beneficiario_nombre ?? '-' }}</td><td class="text-right">Bs {{ number_format($c->monto, 2) }}</td><td class="text-center">{{ ucfirst($c->estado) }}</td></tr>
                @empty
                <tr><td colspan="4" class="text-center">Sin registros</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- PRESUPUESTO --}}
    <div class="section">
        <h2>4. Presupuesto</h2>
        <div class="kpi-grid">
            <div class="kpi"><div class="kpi-label">Cheques Pendientes</div><div class="kpi-value">{{ $reportes['presupuesto']['resumen']['pendientes'] }}</div></div>
            <div class="kpi"><div class="kpi-label">Aprobados (Mes)</div><div class="kpi-value">{{ $reportes['presupuesto']['resumen']['aprobados'] }}</div></div>
            <div class="kpi"><div class="kpi-label">Rechazados (Mes)</div><div class="kpi-value">{{ $reportes['presupuesto']['resumen']['rechazados'] }}</div></div>
            <div class="kpi"><div class="kpi-label">Monto Pendiente</div><div class="kpi-value">Bs {{ number_format($reportes['presupuesto']['resumen']['monto_pendiente'], 2) }}</div></div>
        </div>
        <table>
            <thead><tr><th>N° Cheque</th><th>Beneficiario</th><th>Banco</th><th class="text-right">Monto</th><th>Fecha Emisión</th></tr></thead>
            <tbody>
                @forelse($reportes['presupuesto']['cheques_pendientes'] as $c)
                <tr><td>{{ $c->numero_cheque }}</td><td>{{ $c->ordenPago?->beneficiario_nombre ?? '-' }}</td><td>{{ $c->banco ?? '-' }}</td><td class="text-right">Bs {{ number_format($c->monto, 2) }}</td><td>{{ $c->fecha_emision?->format('d/m/Y') }}</td></tr>
                @empty
                <tr><td colspan="5" class="text-center">Sin registros</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- ADMINISTRACIÓN --}}
    <div class="section">
        <h2>5. Administración</h2>
        <div class="kpi-grid">
            <div class="kpi"><div class="kpi-label">Cheques Pendientes</div><div class="kpi-value">{{ $reportes['administracion']['resumen']['pendientes'] }}</div></div>
            <div class="kpi"><div class="kpi-label">Enviados a Caja (Mes)</div><div class="kpi-value">{{ $reportes['administracion']['resumen']['enviados_caja'] }}</div></div>
            <div class="kpi"><div class="kpi-label">Rechazados (Mes)</div><div class="kpi-value">{{ $reportes['administracion']['resumen']['rechazados'] }}</div></div>
            <div class="kpi"><div class="kpi-label">Monto Pendiente</div><div class="kpi-value">Bs {{ number_format($reportes['administracion']['resumen']['monto_pendiente'], 2) }}</div></div>
        </div>
        <table>
            <thead><tr><th>N° Cheque</th><th>Beneficiario</th><th>Banco</th><th class="text-right">Monto</th><th>Fecha Emisión</th></tr></thead>
            <tbody>
                @forelse($reportes['administracion']['cheques_pendientes'] as $c)
                <tr><td>{{ $c->numero_cheque }}</td><td>{{ $c->ordenPago?->beneficiario_nombre ?? '-' }}</td><td>{{ $c->banco ?? '-' }}</td><td class="text-right">Bs {{ number_format($c->monto, 2) }}</td><td>{{ $c->fecha_emision?->format('d/m/Y') }}</td></tr>
                @empty
                <tr><td colspan="5" class="text-center">Sin registros</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- CAJA --}}
    <div class="section">
        <h2>6. Caja</h2>
        <div class="kpi-grid">
            <div class="kpi"><div class="kpi-label">Por Entregar</div><div class="kpi-value">{{ $reportes['caja']['resumen']['para_entregar'] }}</div></div>
            <div class="kpi"><div class="kpi-label">Entregados Hoy</div><div class="kpi-value">{{ $reportes['caja']['resumen']['entregados_hoy'] }}</div></div>
            <div class="kpi"><div class="kpi-label">Cobrados (Mes)</div><div class="kpi-value">{{ $reportes['caja']['resumen']['cobrados_mes'] }}</div></div>
            <div class="kpi"><div class="kpi-label">Monto Entregado (Mes)</div><div class="kpi-value">Bs {{ number_format($reportes['caja']['resumen']['monto_entregado_mes'], 2) }}</div></div>
        </div>
        <h3>Órdenes por Entregar</h3>
        <table>
            <thead><tr><th>N° Orden</th><th>Beneficiario</th><th>Cheque</th><th class="text-right">Monto</th></tr></thead>
            <tbody>
                @forelse($reportes['caja']['para_entregar'] as $o)
                <tr><td>{{ $o->numero_orden }}</td><td>{{ $o->beneficiario_nombre }}</td><td>{{ $o->cheque?->numero_cheque ?? '-' }}</td><td class="text-right">Bs {{ number_format($o->neto_pagar, 2) }}</td></tr>
                @empty
                <tr><td colspan="4" class="text-center">Sin registros</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- ARCHIVOS --}}
    <div class="section">
        <h2>7. Archivos</h2>
        <div class="kpi-grid">
            <div class="kpi"><div class="kpi-label">Por Archivar</div><div class="kpi-value">{{ $reportes['archivos']['resumen']['por_archivar'] }}</div></div>
            <div class="kpi"><div class="kpi-label">Archivados (Mes)</div><div class="kpi-value">{{ $reportes['archivos']['resumen']['archivados_mes'] }}</div></div>
            <div class="kpi"><div class="kpi-label">Total Archivados</div><div class="kpi-value">{{ $reportes['archivos']['resumen']['archivados_total'] }}</div></div>
        </div>
        <table>
            <thead><tr><th>N° Orden</th><th>Beneficiario</th><th class="text-right">Monto</th></tr></thead>
            <tbody>
                @forelse($reportes['archivos']['por_archivar'] as $o)
                <tr><td>{{ $o->numero_orden }}</td><td>{{ $o->beneficiario_nombre }}</td><td class="text-right">Bs {{ number_format($o->monto_total, 2) }}</td></tr>
                @empty
                <tr><td colspan="3" class="text-center">Sin registros</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- GLOBAL --}}
    <div class="section">
        <h2>8. Resumen Global</h2>
        <div class="kpi-grid">
            <div class="kpi"><div class="kpi-label">Total Órdenes</div><div class="kpi-value">{{ $reportes['global']['resumen']['total_ordenes'] }}</div></div>
            <div class="kpi"><div class="kpi-label">Total Cheques</div><div class="kpi-value">{{ $reportes['global']['resumen']['total_cheques'] }}</div></div>
            <div class="kpi"><div class="kpi-label">Monto Total</div><div class="kpi-value">Bs {{ number_format($reportes['global']['resumen']['monto_total_ordenes'], 2) }}</div></div>
            <div class="kpi"><div class="kpi-label">Beneficiarios</div><div class="kpi-value">{{ $reportes['global']['resumen']['total_beneficiarios'] }}</div></div>
        </div>
        <table>
            <thead><tr><th>Estado</th><th class="text-right">Cantidad</th></tr></thead>
            <tbody>
                @forelse($reportes['global']['ordenes_por_estado'] as $item)
                <tr><td>{{ ucfirst(str_replace('_', ' ', $item->estado)) }}</td><td class="text-right">{{ $item->total }}</td></tr>
                @empty
                <tr><td colspan="2" class="text-center">Sin datos</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="footer">
        <p>Sistema Integral de Pago CPS — Reporte Consolidado por Áreas</p>
        <p>Generado el {{ $reportes['generado_en']->format('d/m/Y H:i') }}</p>
    </div>
</body>
</html>