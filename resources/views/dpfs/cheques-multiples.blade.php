<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Cheques Seleccionados</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 10px; margin: 0; padding: 10px; }
        .page-break { page-break-after: always; }
        .cheque-box { border: 2px solid #374151; padding: 12px; max-width: 720px; margin: 0 auto 10px; }
        .cheque-header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 12px; }
        .banco { font-size: 14px; font-weight: bold; color: #1e3a8a; }
        .numero { font-size: 16px; font-weight: bold; color: #374151; border: 1px solid #374151; padding: 4px 12px; }
        .row { display: flex; align-items: baseline; margin-bottom: 8px; gap: 8px; }
        .label { font-size: 8px; color: #6b7280; white-space: nowrap; }
        .value { border-bottom: 1px solid #374151; flex: 1; padding: 2px 4px; font-weight: bold; }
        .monto-box { border: 2px solid #374151; padding: 6px; text-align: right; font-size: 14px; font-weight: bold; margin: 8px 0; }
        .literal { border-bottom: 1px solid #374151; padding: 4px; margin: 8px 0; font-style: italic; }
        .firma-line { text-align: right; font-size: 10px; }
    </style>
</head>
<body>
    @foreach($cheques as $i => $cheque)
    <div class="cheque-box {{ !$loop->last ? 'page-break' : '' }}">
        <div class="cheque-header">
            <div>
                <div class="banco">CHEQUE N° {{ $cheque->numero_cheque }}</div>
                <div style="font-size:8px; color:#6b7280;">{{ config('app.ciudad') }}, Bolivia</div>
            </div>
            <div class="numero">N° {{ $cheque->numero_cheque }}</div>
        </div>

        <div class="row">
            <span class="label">FECHA:</span>
            <span class="value">{{ $cheque->fecha_emision?->format('d/m/Y') }}</span>
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

        <div class="firma-area" style="margin-top:25px;">
            <div class="firma-line">
                {{ $cheque->emisor->name ?? '________________________' }}<br>
                Firma Autorizada
            </div>
        </div>

        <div style="text-align:center; font-size:7px; color:#9ca3af; margin-top:8px; border-top:1px solid #e5e7eb; padding-top:4px;">
            Generado por Sistema CPS — {{ now()->format('d/m/Y H:i') }}
        </div>
    </div>
    @endforeach
</body>
</html>