<?php
// app/Services/ReporteService.php
namespace App\Services;

use App\Models\{OrdenPago, Cheque, Beneficiario};
use Illuminate\Support\Facades\DB;

class ReporteService
{
    public function generarReporte(array $filtros)
    {
        $tipo = $filtros['tipo_reporte'];
        $fechaDesde = $filtros['fecha_desde'];
        $fechaHasta = $filtros['fecha_hasta'];
        
        switch($tipo) {
            case 'ordenes':
                return $this->reporteOrdenes($fechaDesde, $fechaHasta, $filtros);
            case 'cheques':
                return $this->reporteCheques($fechaDesde, $fechaHasta, $filtros);
            case 'beneficiarios':
                return $this->reporteBeneficiarios($filtros);
            case 'financiero':
                return $this->reporteFinanciero($fechaDesde, $fechaHasta);
            case 'flujo':
                return $this->reporteFlujo($fechaDesde, $fechaHasta, $filtros);
            default:
                return [];
        }
    }
    
    private function reporteOrdenes($fechaDesde, $fechaHasta, $filtros)
    {
        $query = OrdenPago::with(['beneficiario', 'categoriaGasto', 'cheque']);
        
        $query->whereBetween('fecha_orden', [$fechaDesde, $fechaHasta]);
        
        if (!empty($filtros['estado'])) {
            $query->where('estado', $filtros['estado']);
        }
        
        if (!empty($filtros['beneficiario_id'])) {
            $query->where('beneficiario_id', $filtros['beneficiario_id']);
        }
        
        if (!empty($filtros['categoria_id'])) {
            $query->where('categoria_gasto_id', $filtros['categoria_id']);
        }
        
        $ordenes = $query->orderBy('fecha_orden')->get();
        
        $resumen = [
            'total_ordenes' => $ordenes->count(),
            'monto_total' => $ordenes->sum('monto_total'),
            'monto_pagado' => $ordenes->where('estado', 'entregado')->sum('neto_pagar'),
            'monto_pendiente' => $ordenes->whereNotIn('estado', ['entregado', 'cerrado'])->sum('neto_pagar')
        ];
        
        return [
            'ordenes' => $ordenes,
            'resumen' => $resumen,
            'filtros' => $filtros
        ];
    }
    
    private function reporteCheques($fechaDesde, $fechaHasta, $filtros)
    {
        $query = Cheque::with(['ordenPago.beneficiario']);
        
        $query->whereBetween('fecha_emision', [$fechaDesde, $fechaHasta]);
        
        if (!empty($filtros['estado'])) {
            $query->where('estado', $filtros['estado']);
        }
        
        $cheques = $query->orderBy('fecha_emision')->get();
        
        $resumen = [
            'total_cheques' => $cheques->count(),
            'monto_total' => $cheques->sum('monto'),
            'monto_anulado' => $cheques->where('estado', 'anulado')->sum('monto')
        ];
        
        return [
            'cheques' => $cheques,
            'resumen' => $resumen,
            'filtros' => $filtros
        ];
    }

    private function reporteBeneficiarios($filtros)
    {
        $query = Beneficiario::withCount(['ordenesPago' => function ($q) {
            $q->whereNotIn('estado', ['anulado']);
        }])->withSum(['ordenesPago' => function ($q) {
            $q->whereNotIn('estado', ['anulado']);
        }], 'monto_total');

        if (!empty($filtros['tipo_documento'])) {
            $query->where('tipo_documento', $filtros['tipo_documento']);
        }

        $beneficiarios = $query->orderByDesc('ordenes_pago_sum_monto_total')->get();

        $resumen = [
            'total_beneficiarios' => $beneficiarios->count(),
            'monto_total_historico' => $beneficiarios->sum('ordenes_pago_sum_monto_total')
        ];

        return [
            'beneficiarios' => $beneficiarios,
            'resumen' => $resumen,
            'filtros' => $filtros
        ];
    }
    
    private function reporteFinanciero($fechaDesde, $fechaHasta)
    {
        // Reporte financiero consolidado
        $ordenesPorMes = OrdenPago::select(
                DB::raw('DATE_FORMAT(fecha_orden, "%Y-%m") as mes'),
                DB::raw('COUNT(*) as total_ordenes'),
                DB::raw('SUM(monto_total) as monto_bruto'),
                DB::raw('SUM(retencion_7 + retencion_35) as total_retenciones'),
                DB::raw('SUM(neto_pagar) as monto_neto')
            )
            ->whereBetween('fecha_orden', [$fechaDesde, $fechaHasta])
            ->groupBy('mes')
            ->orderBy('mes')
            ->get();
            
        $topBeneficiarios = OrdenPago::select('beneficiario_id', DB::raw('SUM(monto_total) as total'))
            ->with('beneficiario')
            ->whereBetween('fecha_orden', [$fechaDesde, $fechaHasta])
            ->groupBy('beneficiario_id')
            ->orderBy('total', 'desc')
            ->limit(10)
            ->get();
            
        return [
            'ordenes_por_mes' => $ordenesPorMes,
            'top_beneficiarios' => $topBeneficiarios,
            'periodo' => ['desde' => $fechaDesde, 'hasta' => $fechaHasta]
        ];
    }

    private function reporteFlujo($fechaDesde, $fechaHasta, $filtros)
    {
        $ordenes = OrdenPago::with(['beneficiario', 'trackingHistorial.usuario', 'trackingHistorial.areaOrigen', 'trackingHistorial.areaDestino'])
            ->whereBetween('fecha_orden', [$fechaDesde, $fechaHasta]);

        if (!empty($filtros['area'])) {
            $area = $filtros['area'];
            $ordenes->whereHas('trackingHistorial', function($q) use ($area) {
                $q->whereHas('areaOrigen', fn($q) => $q->where('codigo', $area))
                  ->orWhereHas('areaDestino', fn($q) => $q->where('codigo', $area));
            });
        }

        $ordenes = $ordenes->orderBy('fecha_orden', 'desc')->get();

        $resumen = [
            'total_ordenes' => $ordenes->count(),
            'por_estado' => $ordenes->groupBy('estado')->map(fn($g) => $g->count()),
            'por_area' => []
        ];

        foreach ($ordenes as $orden) {
            foreach ($orden->trackingHistorial as $track) {
                $area = $track->areaOrigen?->nombre ?? $track->areaDestino?->nombre ?? 'Sin área';
                $resumen['por_area'][$area] = ($resumen['por_area'][$area] ?? 0) + 1;
            }
        }

        return [
            'ordenes' => $ordenes,
            'resumen' => $resumen,
            'filtros' => $filtros,
            'periodo' => ['desde' => $fechaDesde, 'hasta' => $fechaHasta]
        ];
    }
}