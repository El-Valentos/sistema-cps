<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Cheque N° {{ $cheque->numero_cheque }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; margin: 0; padding: 15px; }
        .cheque-box { padding: 15px; max-width: 720px; }
        .cheque-header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 15px; }
        .banco { font-size: 16px; font-weight: bold; color: #1e3a8a; }
        .numero { font-size: 18px; font-weight: bold; color: #374151; padding: 5px 15px; }
        .row { display: flex; align-items: baseline; margin-bottom: 10px; gap: 10px; }
        .label { font-size: 9px; color: #6b7280; white-space: nowrap; }
        .value { flex: 1; padding: 2px 5px; font-weight: bold; }
        .monto-box { padding: 8px; text-align: right; font-size: 16px; font-weight: bold; margin: 10px 0; }
        .literal { padding: 5px; margin: 10px 0; font-style: italic; }
    </style>
</head>
<body>
    <div class="cheque-box">
        <div class="cheque-header">
            <div>
                <div style="font-size:9px; color:#6b7280;">{{ config('app.ciudad') }}, Bolivia</div>
            </div>
            <div class="numero">N° {{ $cheque->numero_cheque }}</div>
        </div>

        <div class="row">
            <span class="label">FECHA:</span>
            <span class="value">{{ $cheque->fecha_emision?->format('d') }} de {{ ['ENERO','FEBRERO','MARZO','ABRIL','MAYO','JUNIO','JULIO','AGOSTO','SEPTIEMBRE','OCTUBRE','NOVIEMBRE','DICIEMBRE'][$cheque->fecha_emision->format('n') - 1] }} de {{ $cheque->fecha_emision?->format('Y') }}</span>
        </div>

        <div class="row">
            <span class="label">PÁGUESE A LA ORDEN DE:</span>
            <span class="value">
                {{ $cheque->ordenPago->a_la_orden_de ?? ($cheque->ordenPago->beneficiario_nombre . ' ' . $cheque->ordenPago->beneficiario_apellidos) }}
            </span>
        </div>

        <div class="monto-box">
            Bs. {{ number_format($cheque->monto, 2) }}
        </div>

        <div class="literal">
            LA SUMA DE: {{ strtoupper($cheque->monto_literal) }}
        </div>

        <div class="row">
            <span class="label">CONCEPTO:</span>
            <span class="value">{{ $cheque->ordenPago->concepto }}</span>
        </div>

        <div class="firma-area">
            <br><br>
            <div class="firma-line">
                {{ $cheque->emisor->name ?? '________________________' }}<br>
                Firma Autorizada
            </div>
        </div>

        <div style="text-align:center; font-size:8px; color:#9ca3af; margin-top:10px; padding-top:5px;">
            Generado por Sistema CPS — {{ now()->format('d/m/Y H:i') }}
        </div>
    </div>
</body>
</html>
