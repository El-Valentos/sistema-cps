<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Orden de Pago N° {{ $ordenPago->numero_orden }}</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; color: #333; }
        .container { width: 100%; max-width: 800px; margin: 20px auto; border: 2px solid #000; padding: 0; }

        /* Table Styles para todo */
        table { width: 100%; border-collapse: collapse; }
        td { border: 0.5px solid #ccc; padding: 8px; vertical-align: top; }
        .label { width: 120px; text-align: right; font-weight: normal; }
        .content { font-weight: bold; }

        /* Header compatibilidad DomPDF (Flexbox no soportado adecuadamente en DomPDF) */
        .header-table { width: 100%; border-collapse: collapse; border-bottom: 1px solid #000; margin-bottom: 0; }
        .header-table td { padding: 5px; vertical-align: middle; border: none; }
        .logo-td { width: 80px; border-right: 1px solid #000 !important; text-align: center; }
        .title-td { text-align: center; font-weight: bold; }
        .ref-td { width: 150px; text-align: center; border-left: 1px solid #000 !important; }

        /* Specific formatting */
        .sub-header { font-size: 10px; font-weight: normal; display: block; margin-top: 4px; }
        .footer-sign { margin-top: 50px; text-align: center; font-weight: bold; border: none; }
        .elaborado { font-size: 8px; padding: 4px; border: none; border-top: 1px solid #000; }
    </style>
</head>
<body>

<div class="container">
    <table class="header-table">
        <tr>
            <td class="logo-td">
                <!-- Asegúrate de usar la ruta correcta a tu imagen (ej: public_path('images/logo.png')) -->
                <img src="{{ public_path('assets/iconcaja.jpeg') }}" alt="Logo CPS" style="width:100%;">
            </td>
            <td class="title-td">
                <span style="font-size: 18px;">ORDEN DE PAGO</span><br>
                <span class="sub-header">JEFATURA DEPARTAMENTAL ADM. FINANCIERA</span>
            </td>
            <td class="ref-td">
                <div style="border-bottom: 1px solid #000; padding-bottom: 2px;">OP N° {{ $ordenPago->numero_orden }} / {{ $ordenPago->gestion }}</div>
                <div style="font-size: 10px; margin-top: 2px;">{{ $ordenPago->ciudad }}</div>
            </td>
        </tr>
    </table>

    <table>
        <tr>
            <td class="label">FECHA:</td>
            <td class="content" colspan="3">
                @php
                    $fecha = now()->locale('es')->translatedFormat('l, d \d\e F \d\e Y');
                @endphp
                {{ ucfirst($fecha) }}
            </td>
        </tr>
        <tr>
            <td class="label">A:</td>
            <td class="content" colspan="3">
                {{ $ordenPago->liquidador_texto ?? $ordenPago->liquidador->name ?? 'Sra. Mabel Milenka Guardia Ramirez' }} <br>
                LIQUIDADOR <br>
                <span style="font-weight: normal; font-size: 11px;">Sírvase efectuar la cancelación de:</span>
            </td>
        </tr>
        <tr>
            <td class="label">Monto:</td>
            <td class="content">Bs. {{ number_format($ordenPago->neto_pagar, 2) }}</td>
            <td></td><td></td>
        </tr>
        <tr>
            <td class="label">A la orden de:</td>
            <td class="content">{{ $ordenPago->a_la_orden_de ?? $ordenPago->beneficiario_nombre }}</td>
            <td></td><td></td>
        </tr>
        <tr>
            <td class="label">Empresa:</td>
            <td class="content" colspan="3">{{ $ordenPago->beneficiario_nombre }}</td>
        </tr>
        <tr>
            <td class="label">Por concepto de:</td>
            <td class="content" colspan="3">{{ $ordenPago->concepto }} <br><br><br><br></td>
        </tr>
        <tr>
            <td class="label">Con respaldo de:</td>
            <td class="content" colspan="3">
                @if($ordenPago->documentosAdjuntos->count() > 0)
                    DOCUMENTACIÓN ADJUNTA ({{ $ordenPago->documentosAdjuntos->count() }} archivo(s))
                @elseif($ordenPago->tiene_respaldo)
                    DOCUMENTACIÓN ADJUNTA
                @else
                    SIN ADJUNTAR
                @endif
                | Fojas {{ $ordenPago->numero_fojas ?? 0 }}
            </td>
        </tr>
    </table>

    <div style="padding: 40px 0 10px 0; text-align: center;">
        <div style="width: 300px; margin: 0 auto; border-top: 1px solid #000; padding-top: 5px;">
            <strong>LIC. JUAN CARLOS BORDA</strong><br>
            JEFE DEPTAL. ADMTIVO. FINANCIERO
        </div>
    </div>
    <div class="elaborado">ELABORADO: {{ auth()->check() ? auth()->user()->name : 'KDMB' }}</div>
</div>

</body>
</html>
