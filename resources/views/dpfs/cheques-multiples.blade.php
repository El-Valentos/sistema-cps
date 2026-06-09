<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Cheques Seleccionados</title>
    <style>
        @page { size: letter; margin: 12mm 15mm; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 9px; margin: 0; padding: 0; color: #000; }
        table { width: 100%; border-collapse: collapse; table-layout: fixed; }
        td { vertical-align: top; padding: 0; }
        .separator { border-bottom: 0.5mm dashed #bbb; }
        .num-cheque { text-align: right; font-size: 11px; font-weight: bold; margin-bottom: 3mm; }
        .fecha { margin-bottom: 2mm; }
        .paguese { margin-bottom: 2mm; font-size: 10px; }
        .monto { text-align: right; font-size: 12px; font-weight: bold; margin-bottom: 2mm; }
        .literal { font-style: italic; font-size: 9px; }
    </style>
</head>
<body>
    @php
        $total = count($cheques);
        $pageH = 279.4;
        $marginV = 24;
        $availH = $pageH - $marginV;
        $rowH = $availH / $total;
        $meses = ['ENERO','FEBRERO','MARZO','ABRIL','MAYO','JUNIO','JULIO','AGOSTO','SEPTIEMBRE','OCTUBRE','NOVIEMBRE','DICIEMBRE'];
    @endphp
    <table>
        @foreach($cheques as $cheque)
        <tr>
            <td style="height: {{ $rowH }}mm; width: 100%; {{ $loop->last ? '' : 'border-bottom: 0.3mm dashed #ccc;' }}">
                <div style="display: inline-block; width: 100%;">
                    <div class="num-cheque">N° {{ $cheque->numero_cheque }}</div>

                    <div class="fecha">
                        {{ config('app.ciudad') }},
                        {{ $cheque->fecha_emision?->format('d') }} de {{ $meses[$cheque->fecha_emision->format('n') - 1] }} de {{ $cheque->fecha_emision?->format('Y') }}
                    </div>

                    <div class="paguese">
                        <strong>PÁGUESE A LA ORDEN DE:</strong>
                        {{ strtoupper($cheque->ordenPago->a_la_orden_de ?? ($cheque->ordenPago->beneficiario_nombre . ' ' . $cheque->ordenPago->beneficiario_apellidos)) }}
                    </div>

                    <div class="monto">Bs. {{ number_format($cheque->monto, 2, '.', '') }}</div>

                    <div class="literal">*** {{ strtoupper($cheque->monto_literal) }} ***</div>
                </div>
            </td>
        </tr>
        @endforeach
    </table>
</body>
</html>
