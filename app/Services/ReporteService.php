<?php
// app/Services/ReporteService.php
namespace App\Services;

use App\Models\{OrdenPago, Cheque, Beneficiario, User, Area};
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

    public function resumenConsolidado()
    {
        $now = now();

        return [
            'tesoreria'     => $this->reporteTesoreria(),
            'financiera'    => $this->reporteFinancieraArea(),
            'contabilidad'  => $this->reporteContabilidadArea(),
            'presupuesto'   => $this->reportePresupuestoArea(),
            'administracion' => $this->reporteAdministracionArea(),
            'caja'          => $this->reporteCajaArea(),
            'archivos'      => $this->reporteArchivosArea(),
            'global'        => $this->reporteGlobal(),
            'generado_en'   => $now,
        ];
    }

    private function reporteTesoreria()
    {
        $ordenes = OrdenPago::whereIn('estado', ['pendiente_tesoreria', 'rechazado_financiera'])
            ->with(['beneficiario', 'categoriaGasto'])
            ->orderBy('fecha_orden', 'desc')
            ->limit(10)
            ->get();

        $enFlujo = OrdenPago::whereIn('estado', [
            'enviado_financiera', 'enviado_contabilidad', 'cheque_generado',
            'enviado_presupuesto', 'enviado_financiera_cheque', 'enviado_administracion',
            'en_caja', 'entregado', 'cobrado', 'revalidado',
        ]);

        $resumen = [
            'pendientes'    => OrdenPago::where('estado', 'pendiente_tesoreria')->count(),
            'rechazados'    => OrdenPago::where('estado', 'rechazado_financiera')->count(),
            'en_flujo'      => $enFlujo->count(),
            'total_mes'     => OrdenPago::whereMonth('fecha_orden', now()->month)
                                ->whereYear('fecha_orden', now()->year)->count(),
            'monto_mes'     => OrdenPago::whereMonth('fecha_orden', now()->month)
                                ->whereYear('fecha_orden', now()->year)->sum('monto_total'),
        ];

        return [
            'ordenes_pendientes' => $ordenes,
            'resumen' => $resumen,
        ];
    }

    private function reporteFinancieraArea()
    {
        $ordenesPendientes = OrdenPago::where('estado', 'enviado_financiera')
            ->with(['beneficiario', 'categoriaGasto'])
            ->orderBy('fecha_orden', 'desc')
            ->limit(10)
            ->get();

        $chequesPendientes = Cheque::where('estado', 'enviado_financiera_cheque')
            ->with(['ordenPago.beneficiario'])
            ->orderBy('fecha_emision', 'desc')
            ->limit(10)
            ->get();

        $resumen = [
            'ordenes_pendientes'  => OrdenPago::where('estado', 'enviado_financiera')->count(),
            'ordenes_aprobadas'   => OrdenPago::where('estado', 'enviado_contabilidad')
                                        ->whereMonth('fecha_aprobacion', now()->month)
                                        ->whereYear('fecha_aprobacion', now()->year)
                                        ->count(),
            'ordenes_rechazadas'  => OrdenPago::where('estado', 'rechazado_financiera')
                                        ->whereMonth('fecha_orden', now()->month)
                                        ->whereYear('fecha_orden', now()->year)
                                        ->count(),
            'cheques_pendientes'  => Cheque::where('estado', 'enviado_financiera_cheque')->count(),
            'cheques_aprobados'   => Cheque::where('estado', 'enviado_administracion')
                                        ->whereMonth('fecha_emision', now()->month)
                                        ->whereYear('fecha_emision', now()->year)
                                        ->count(),
            'monto_ordenes_pendientes' => OrdenPago::where('estado', 'enviado_financiera')->sum('neto_pagar'),
        ];

        return [
            'ordenes_pendientes' => $ordenesPendientes,
            'cheques_pendientes' => $chequesPendientes,
            'resumen' => $resumen,
        ];
    }

    private function reporteContabilidadArea()
    {
        $ordenesPendientes = OrdenPago::where('estado', 'enviado_contabilidad')
            ->with(['beneficiario', 'categoriaGasto'])
            ->orderBy('fecha_orden', 'desc')
            ->limit(10)
            ->get();

        $ultimosCheques = Cheque::with(['ordenPago.beneficiario'])
            ->orderBy('fecha_emision', 'desc')
            ->limit(10)
            ->get();

        $resumen = [
            'ordenes_pendientes'  => OrdenPago::where('estado', 'enviado_contabilidad')->count(),
            'cheques_emitidos'    => Cheque::whereMonth('fecha_emision', now()->month())
                                      ->whereYear('fecha_emision', now()->year())->count(),
            'cheques_enviados_presupuesto' => Cheque::where('estado', 'enviado_presupuesto')->count(),
            'cheques_enviados_admin'       => Cheque::where('estado', 'enviado_administracion')->count(),
            'cheques_anulados_mes'         => Cheque::where('estado', 'anulado')
                                              ->whereMonth('fecha_emision', now()->month())
                                              ->whereYear('fecha_emision', now()->year())->count(),
            'monto_cheques_mes'            => Cheque::whereMonth('fecha_emision', now()->month())
                                              ->whereYear('fecha_emision', now()->year())->sum('monto'),
            'entregados_por_revisar'       => OrdenPago::where('estado', 'entregado_contabilidad')->count(),
        ];

        return [
            'ordenes_pendientes' => $ordenesPendientes,
            'ultimos_cheques'    => $ultimosCheques,
            'resumen'            => $resumen,
        ];
    }

    private function reportePresupuestoArea()
    {
        $chequesPendientes = Cheque::where('estado', 'enviado_presupuesto')
            ->with(['ordenPago.beneficiario'])
            ->orderBy('fecha_emision', 'desc')
            ->limit(10)
            ->get();

        $resumen = [
            'pendientes'  => Cheque::where('estado', 'enviado_presupuesto')->count(),
            'aprobados'   => Cheque::where('estado', 'enviado_financiera_cheque')
                              ->whereMonth('fecha_emision', now()->month())
                              ->whereYear('fecha_emision', now()->year())->count(),
            'rechazados'  => Cheque::where('estado', 'rechazado_presupuesto')
                              ->whereMonth('fecha_emision', now()->month())
                              ->whereYear('fecha_emision', now()->year())->count(),
            'monto_pendiente' => Cheque::where('estado', 'enviado_presupuesto')->sum('monto'),
        ];

        return [
            'cheques_pendientes' => $chequesPendientes,
            'resumen' => $resumen,
        ];
    }

    private function reporteAdministracionArea()
    {
        $chequesPendientes = Cheque::where('estado', 'enviado_administracion')
            ->with(['ordenPago.beneficiario'])
            ->orderBy('fecha_emision', 'desc')
            ->limit(10)
            ->get();

        $resumen = [
            'pendientes'      => Cheque::where('estado', 'enviado_administracion')->count(),
            'enviados_caja'   => Cheque::where('estado', 'en_caja')
                                  ->whereMonth('fecha_emision', now()->month())
                                  ->whereYear('fecha_emision', now()->year())->count(),
            'rechazados'      => Cheque::where('estado', 'rechazado_administracion')
                                  ->whereMonth('fecha_emision', now()->month())
                                  ->whereYear('fecha_emision', now()->year())->count(),
            'monto_pendiente' => Cheque::where('estado', 'enviado_administracion')->sum('monto'),
        ];

        return [
            'cheques_pendientes' => $chequesPendientes,
            'resumen' => $resumen,
        ];
    }

    private function reporteCajaArea()
    {
        $paraEntregar = OrdenPago::where('estado', 'en_caja')
            ->with(['beneficiario', 'cheque'])
            ->orderBy('fecha_orden', 'desc')
            ->limit(10)
            ->get();

        $ultimasEntregas = OrdenPago::whereIn('estado', ['entregado', 'cobrado', 'revalidado'])
            ->with(['beneficiario', 'cheque'])
            ->orderBy('updated_at', 'desc')
            ->limit(10)
            ->get();

        $resumen = [
            'para_entregar'    => OrdenPago::where('estado', 'en_caja')->count(),
            'entregados_hoy'   => OrdenPago::where('estado', 'entregado')
                                   ->whereDate('updated_at', now()->today())->count(),
            'entregados_mes'   => OrdenPago::whereIn('estado', ['entregado', 'cobrado', 'revalidado'])
                                   ->whereMonth('updated_at', now()->month())
                                   ->whereYear('updated_at', now()->year())->count(),
            'cobrados_mes'     => OrdenPago::where('estado', 'cobrado')
                                   ->whereMonth('updated_at', now()->month())
                                   ->whereYear('updated_at', now()->year())->count(),
            'revalidados_mes'  => OrdenPago::where('estado', 'revalidado')
                                   ->whereMonth('updated_at', now()->month())
                                   ->whereYear('updated_at', now()->year())->count(),
            'monto_entregado_mes' => OrdenPago::whereIn('estado', ['entregado', 'cobrado', 'revalidado'])
                                       ->whereMonth('updated_at', now()->month())
                                       ->whereYear('updated_at', now()->year())->sum('neto_pagar'),
        ];

        return [
            'para_entregar'   => $paraEntregar,
            'ultimas_entregas' => $ultimasEntregas,
            'resumen'          => $resumen,
        ];
    }

    private function reporteArchivosArea()
    {
        $porArchivar = OrdenPago::where('estado', 'enviado_archivos')
            ->with(['beneficiario'])
            ->orderBy('fecha_orden', 'desc')
            ->limit(10)
            ->get();

        $archivados = OrdenPago::where('estado', 'archivado')
            ->with(['beneficiario'])
            ->orderBy('fecha_cierre', 'desc')
            ->limit(10)
            ->get();

        $resumen = [
            'por_archivar'    => OrdenPago::where('estado', 'enviado_archivos')->count(),
            'archivados_mes'  => OrdenPago::where('estado', 'archivado')
                                  ->whereMonth('fecha_cierre', now()->month())
                                  ->whereYear('fecha_cierre', now()->year())->count(),
            'archivados_total' => OrdenPago::where('estado', 'archivado')->count(),
        ];

        return [
            'por_archivar' => $porArchivar,
            'archivados'   => $archivados,
            'resumen'      => $resumen,
        ];
    }

    private function reporteGlobal()
    {
        $ordenesPorEstado = OrdenPago::select('estado', DB::raw('COUNT(*) as total'))
            ->groupBy('estado')
            ->orderBy('total', 'desc')
            ->get();

        $mesActual = now()->month;
        $anioActual = now()->year;

        $resumen = [
            'total_ordenes'           => OrdenPago::count(),
            'ordenes_mes'             => OrdenPago::whereMonth('fecha_orden', $mesActual)
                                          ->whereYear('fecha_orden', $anioActual)->count(),
            'total_cheques'           => Cheque::count(),
            'cheques_mes'             => Cheque::whereMonth('fecha_emision', $mesActual)
                                          ->whereYear('fecha_emision', $anioActual)->count(),
            'monto_total_ordenes'     => OrdenPago::sum('monto_total'),
            'monto_total_neto'        => OrdenPago::sum('neto_pagar'),
            'monto_cheques_mes'       => Cheque::whereMonth('fecha_emision', $mesActual)
                                          ->whereYear('fecha_emision', $anioActual)->sum('monto'),
            'total_beneficiarios'     => Beneficiario::count(),
            'usuarios_activos'        => User::where('activo', true)->count(),
        ];

        return [
            'ordenes_por_estado' => $ordenesPorEstado,
            'resumen' => $resumen,
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