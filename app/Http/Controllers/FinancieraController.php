<?php

namespace App\Http\Controllers;

use App\Models\OrdenPago;
use App\Models\Cheque;
use App\Services\TrackingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FinancieraController extends Controller
{
    public function __construct(
        protected TrackingService $trackingService,
    ) {
        $this->middleware('auth');
    }

    public function index()
    {
        // Órdenes nuevas de Tesorería y reenviadas
        $ordenes = OrdenPago::whereIn('estado', ['enviado_financiera', 'reenviado_financiera'])
            ->with(['beneficiario', 'categoriaGasto', 'creador'])
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('financiera.index', compact('ordenes'));
    }

    // Ver cheques que vienen de Presupuesto
    public function verCheques()
    {
        $cheques = Cheque::whereHas('ordenPago', function ($query) {
                $query->where('estado', 'enviado_financiera_cheque');
            })
            ->with(['ordenPago.beneficiario', 'ordenPago.categoriaGasto'])
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('financiera.cheques', compact('cheques'));
    }

    // Aprobar cheque y enviar a Administración
    public function aprobarCheque(Cheque $cheque)
    {
        try {
            DB::beginTransaction();

            $ordenPago = $cheque->ordenPago;
            $ordenPago->update(['estado' => 'enviado_administracion']);

            $this->trackingService->registrarEvento(
                $ordenPago,
                'aprobacion_cheque_financiera',
                'enviado_financiera_cheque',
                'enviado_administracion',
                'Cheque aprobado por Financiera y enviado a Administración'
            );

            DB::commit();

            $report = app(\App\Services\ReporteEnvioService::class)->generar(
                collect([$cheque]), 'enviado_administracion', 'cheque'
            );

            return redirect()->route('financiera.cheques')
                ->with('success', 'Cheque aprobado y enviado a Administración exitosamente')
                ->with('download_report', $report);

        } catch (\Exception $e) {
            DB::rollback();
            return back()->with('error', 'Error al aprobar el cheque: ' . $e->getMessage());
        }
    }

    // Rechazar cheque
    public function rechazarCheque(Request $request, Cheque $cheque)
    {
        $request->validate([
            'motivo_rechazo' => 'required|string|min:10'
        ]);

        try {
            DB::beginTransaction();

            $ordenPago = $cheque->ordenPago;
            $estadoAnterior = $ordenPago->estado;
            $ordenPago->update([
                'estado' => 'rechazado_financiera_cheque',
                'observaciones' => $request->motivo_rechazo
            ]);

            $this->trackingService->registrarEvento(
                $ordenPago,
                'rechazo_cheque_financiera',
                $estadoAnterior,
                'rechazado_financiera_cheque',
                'Cheque rechazado por Financiera. Motivo: ' . $request->motivo_rechazo
            );

            DB::commit();

            return redirect()->route('financiera.cheques')
                ->with('success', 'Cheque rechazado exitosamente');

        } catch (\Exception $e) {
            DB::rollback();
            return back()->with('error', 'Error al rechazar el cheque: ' . $e->getMessage());
        }
    }

    public function aprobar(OrdenPago $ordenPago)
    {
        // Estados desde los cuales se permite aprobar
        $estadosPermitidos = ['enviado_financiera', 'reenviado_financiera'];

        if (!in_array($ordenPago->estado, $estadosPermitidos)) {
            return back()->with('error', 'La orden no puede ser aprobada. Estado actual: ' . $ordenPago->estado);
        }

        try {
            DB::beginTransaction();

            $estadoAnterior = $ordenPago->estado;

            $ordenPago->update([
                'estado' => 'enviado_contabilidad'
            ]);

            $this->trackingService->registrarEvento(
                $ordenPago,
                'envio_contabilidad',
                $estadoAnterior,
                'enviado_contabilidad',
                'Orden aprobada por Financiera y enviada a Contabilidad'
            );

            DB::commit();

            $report = app(\App\Services\ReporteEnvioService::class)->generar(
                collect([$ordenPago]), 'enviado_contabilidad', 'orden'
            );

            return redirect()->route('financiera.index')
                ->with('success', 'Orden enviada a Contabilidad exitosamente')
                ->with('download_report', $report);

        } catch (\Exception $e) {
            DB::rollback();
            return back()->with('error', 'Error al aprobar la orden: ' . $e->getMessage());
        }
    }

    public function rechazar(Request $request, OrdenPago $ordenPago)
    {
        $request->validate([
            'motivo_rechazo' => 'required|string|min:10'
        ]);

        // Estados desde los cuales se permite rechazar (incluye reenviadas)
        $estadosPermitidos = ['enviado_financiera', 'reenviado_financiera'];

        if (!in_array($ordenPago->estado, $estadosPermitidos)) {
            return back()->with('error', 'La orden no puede ser rechazada. Estado actual: ' . $ordenPago->estado);
        }

        try {
            DB::beginTransaction();

            $estadoAnterior = $ordenPago->estado;

            $ordenPago->update([
                'estado' => 'rechazado_financiera',
                'observaciones' => $request->motivo_rechazo
            ]);

            $this->trackingService->registrarEvento(
                $ordenPago,
                'rechazo_financiera',
                $estadoAnterior,
                'rechazado_financiera',
                'Orden rechazada por Financiera. Motivo: ' . $request->motivo_rechazo
            );

            DB::commit();

            return redirect()->route('financiera.index')
                ->with('success', 'Orden rechazada exitosamente');

        } catch (\Exception $e) {
            DB::rollback();
            return back()->with('error', 'Error al rechazar la orden: ' . $e->getMessage());
        }
    }

    public function aprobarMasivo(Request $request)
    {
        $ids = $request->input('ordenes', []);
        if (empty($ids)) return back()->with('warning', 'No se seleccionaron órdenes');

        try {
            DB::beginTransaction();
            $ordenes = OrdenPago::whereIn('id', $ids)
                ->whereIn('estado', ['enviado_financiera', 'reenviado_financiera'])
                ->get();

            $cont = 0;
            foreach ($ordenes as $orden) {
                $estadoAnterior = $orden->estado;
                $orden->update(['estado' => 'enviado_contabilidad']);
                $this->trackingService->registrarEvento(
                    $orden,
                    'envio_contabilidad',
                    $estadoAnterior,
                    'enviado_contabilidad',
                    'Orden aprobada masivamente por Financiera y enviada a Contabilidad'
                );
                $cont++;
            }
            DB::commit();
            $report = app(\App\Services\ReporteEnvioService::class)->generar(
                $ordenes, 'enviado_contabilidad', 'orden'
            );
            return back()->with('success', "Se han enviado {$cont} órdenes a Contabilidad correctamente")
                ->with('download_report', $report);
        } catch (\Exception $e) {
            DB::rollback();
            return back()->with('error', 'Error al procesar el envío masivo: ' . $e->getMessage());
        }
    }

    public function aprobarChequeMasivo(Request $request)
    {
        $ids = $request->input('cheques', []);
        if (empty($ids)) return back()->with('warning', 'No se seleccionaron cheques');

        try {
            DB::beginTransaction();
            $cheques = Cheque::whereIn('id', $ids)->get();
            $cont = 0;
            foreach ($cheques as $cheque) {
                $ordenPago = $cheque->ordenPago;
                if ($ordenPago->estado === 'enviado_financiera_cheque') {
                    $ordenPago->update(['estado' => 'enviado_administracion']);
                    $this->trackingService->registrarEvento(
                        $ordenPago,
                        'aprobacion_cheque_financiera',
                        'enviado_financiera_cheque',
                        'enviado_administracion',
                        'Cheque aprobado masivamente por Financiera y enviado a Administración'
                    );
                    $cont++;
                }
            }
            DB::commit();
            $report = app(\App\Services\ReporteEnvioService::class)->generar(
                $cheques, 'enviado_administracion', 'cheque'
            );
            return back()->with('success', "Se han aprobado {$cont} cheques correctamente")
                ->with('download_report', $report);
        } catch (\Exception $e) {
            DB::rollback();
            return back()->with('error', 'Error al procesar el envío masivo: ' . $e->getMessage());
        }
    }
}