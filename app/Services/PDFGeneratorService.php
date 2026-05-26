<?php

namespace App\Services;

use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\{OrdenPago, Cheque};
use Illuminate\Support\Facades\Storage;

class PDFGeneratorService
{
    public function generarOrdenPago(OrdenPago $orden): string
    {
        $orden->load(['beneficiario', 'categoriaGasto', 'liquidador']);

        $pdf = PDF::loadView('dpfs.orden-pago', compact('orden'));
        $pdf->setPaper('letter', 'portrait');

        $filename = "orden_pago_{$orden->numero_orden}.pdf";
        $path = "pdfs/ordenes/{$filename}";

        Storage::disk('public')->put($path, $pdf->output());

        return $path;
    }

    public function generarCheque(Cheque $cheque): string
    {
        $cheque->load(['ordenPago.beneficiario']);

        $pdf = PDF::loadView('dpfs.cheque', compact('cheque'));
        $pdf->setPaper('letter', 'landscape');

        $filename = "cheque_{$cheque->numero_cheque}.pdf";
        $path = "pdfs/cheques/{$filename}";

        Storage::disk('public')->put($path, $pdf->output());

        return $path;
    }

    public function convertirNumeroALiteral(float $numero): string
    {
        $enteros = floor($numero);
        $centavos = round(($numero - $enteros) * 100);

        if ($enteros == 0) {
            $literal = 'CERO';
        } else {
            $literal = strtoupper($this->convertirEntero((int)$enteros));
        }

        $strCentavos = str_pad($centavos, 2, '0', STR_PAD_LEFT);
        return "{$literal} {$strCentavos}/100 BOLIVIANOS";
    }

    private function convertirEntero(int $numero): string
    {
        $unidades = ['', 'UN', 'DOS', 'TRES', 'CUATRO', 'CINCO', 'SEIS', 'SIETE', 'OCHO', 'NUEVE'];
        $decenas = ['', 'DIEZ', 'VEINTE', 'TREINTA', 'CUARENTA', 'CINCUENTA', 'SESENTA', 'SETENTA', 'OCHENTA', 'NOVENTA'];
        $centenas = ['', 'CIENTO', 'DOSCIENTOS', 'TRESCIENTOS', 'CUATROCIENTOS', 'QUINIENTOS', 'SEISCIENTOS', 'SETECIENTOS', 'OCHOCIENTOS', 'NOVECIENTOS'];
        $especiales = [11 => 'ONCE', 12 => 'DOCE', 13 => 'TRECE', 14 => 'CATORCE', 15 => 'QUINCE', 16 => 'DIECISEIS', 17 => 'DIECISIETE', 18 => 'DIECIOCHO', 19 => 'DIECINUEVE'];

        if ($numero == 0) return '';
        if ($numero == 100) return 'CIEN';

        if ($numero < 10) return $unidades[$numero];
        if ($numero < 20) {
            if ($numero == 10) return 'DIEZ';
            return $especiales[$numero];
        }
        if ($numero < 100) {
            $decena = intdiv($numero, 10);
            $unidad = $numero % 10;
            if ($unidad == 0) return $decenas[$decena];
            if ($decena == 2) return 'VEINTI' . $unidades[$unidad];
            return $decenas[$decena] . ' Y ' . $unidades[$unidad];
        }
        if ($numero < 1000) {
            $centena = intdiv($numero, 100);
            $resto = $numero % 100;
            if ($resto == 0) return $centenas[$centena];
            return $centenas[$centena] . ' ' . $this->convertirEntero($resto);
        }
        if ($numero < 1000000) {
            $miles = intdiv($numero, 1000);
            $resto = $numero % 1000;
            $strMiles = ($miles == 1) ? 'MIL' : $this->convertirEntero($miles) . ' MIL';
            if ($resto == 0) return $strMiles;
            return $strMiles . ' ' . $this->convertirEntero($resto);
        }
        if ($numero < 1000000000) {
            $millones = intdiv($numero, 1000000);
            $resto = $numero % 1000000;
            $strMillones = ($millones == 1) ? 'UN MILLON' : $this->convertirEntero($millones) . ' MILLONES';
            if ($resto == 0) return $strMillones;
            return $strMillones . ' ' . $this->convertirEntero($resto);
        }
        return 'NUMERO DEMASIADO GRANDE';
    }
}
