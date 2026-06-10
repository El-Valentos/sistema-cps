<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Cheques</title>
    <style>
        @page { size: letter; margin: 0; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 8px; margin: 0; padding: 0; color: #000; }
        .page { page-break-after: always; width: 215.9mm; height: 279.4mm; position: relative; }
        .page:last-child { page-break-after: avoid; }
        .cheque { position: absolute; left: 26.95mm; width: 162mm; height: 69.8mm; }
        .ch-fecha { position: absolute; left: 47mm; top: 12mm; width: 63mm; padding: 0 2mm; font-size: 8.5px; }
        .ch-paguese { position: absolute; left: 16mm; top: 21mm; width: 143mm; padding: 0 2mm; font-size: 8.5px; font-weight: bold; }
        .ch-monto { position: absolute; left: 122mm; top: 15mm; width: 30mm; padding: 0 2mm; text-align: right; font-size: 9px; font-weight: bold; }
        .ch-suma { position: absolute; left: 16mm; top: 28mm; width: 143mm; padding: 0 2mm; font-size: 8px; font-style: italic; }
    </style>
</head>
<body>
    @php
        $chunks = $cheques->chunk(4);
        $meses = ['ENERO','FEBRERO','MARZO','ABRIL','MAYO','JUNIO','JULIO','AGOSTO','SEPTIEMBRE','OCTUBRE','NOVIEMBRE','DICIEMBRE'];
    @endphp

    @foreach($chunks as $chunk)
        <div class="page">
            @foreach($chunk as $i => $cheque)
                <div class="cheque" style="top: {{ $i * 69.8 }}mm;">
                    <div class="ch-fecha">
                        {{ config('app.ciudad') }},
                        {{ $cheque->fecha_emision?->format('d') }} de {{ $meses[$cheque->fecha_emision->format('n') - 1] }} de {{ $cheque->fecha_emision?->format('Y') }}
                    </div>
                    <div class="ch-paguese">
                        {{ strtoupper($cheque->ordenPago->a_la_orden_de ?? ($cheque->ordenPago->beneficiario_nombre . ' ' . $cheque->ordenPago->beneficiario_apellidos)) }}
                    </div>
                    <div class="ch-monto">Bs. {{ number_format($cheque->monto, 2, '.', '') }}</div>
                    <div class="ch-suma">{{ strtoupper($cheque->monto_literal) }}</div>
                </div>
            @endforeach
        </div>
    @endforeach
</body>
</html>
