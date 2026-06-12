<?php

namespace App\Services;

use App\Models\Cheque;
use App\Models\OrdenPago;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ReporteEnvioService
{
    public function generar(Collection $items, string $destinoState, string $tipo = 'orden'): string
    {
        $origen = auth()->user()->roles->first()->name ?? 'Sistema';
        $destino = $this->stateToArea($destinoState);

        $totalMonto = $items->sum(function ($item) {
            return $item instanceof Cheque ? $item->monto : $item->neto_pagar;
        });

        $data = [
            'origen' => $origen,
            'destino' => $destino,
            'items' => $items,
            'tipo' => $tipo,
            'totalItems' => $items->count(),
            'totalMonto' => $totalMonto,
            'fecha' => now()->format('d/m/Y H:i'),
            'usuario' => auth()->user()->name,
        ];

        $pdf = Pdf::loadView('dpfs.envio-reporte', $data);
        $pdf->setPaper('letter', 'portrait');

        $filename = 'envio_' . Str::random(16) . '.pdf';
        $path = "public/tmp/{$filename}";
        Storage::put($path, $pdf->output());

        return $filename;
    }

    private function stateToArea(string $state): string
    {
        return match ($state) {
            'enviado_financiera', 'reenviado_financiera', 'enviado_financiera_cheque' => 'Financiera',
            'enviado_contabilidad' => 'Contabilidad',
            'enviado_presupuesto' => 'Presupuesto',
            'enviado_administracion' => 'Administración',
            'en_caja' => 'Caja',
            'entregado_contabilidad' => 'Contabilidad (Archivos)',
            'enviado_archivos', 'archivado' => 'Archivos',
            default => $state,
        };
    }
}
