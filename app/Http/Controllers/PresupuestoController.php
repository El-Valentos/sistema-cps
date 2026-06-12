<?php

namespace App\Http\Controllers;

use App\Models\OrdenPago;
use App\Models\Cheque;
use App\Services\TrackingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PresupuestoController extends Controller
{
    public function __construct(
        protected TrackingService $trackingService,
    ) {
        $this->middleware('auth');
    }

    public function index()
    {
        // Presupuesto ve cheques recién generados y rechazados por Financiera
        $cheques = Cheque::whereHas('ordenPago', function ($query) {
                $query->whereIn('estado', ['enviado_presupuesto', 'rechazado_financiera_cheque']);
            })
            ->with(['ordenPago.beneficiario', 'ordenPago.categoriaGasto'])
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('presupuesto.index', compact('cheques'));
    }

    public function show(Cheque $cheque)
    {
        $cheque->load(['ordenPago.beneficiario', 'ordenPago.categoriaGasto']);
        return view('presupuesto.show', compact('cheque'));
    }

    public function aprobar(Cheque $cheque)
    {
        try {
            DB::beginTransaction();

            $ordenPago = $cheque->ordenPago;
            $estadoAnterior = $ordenPago->estado;
            $ordenPago->update(['estado' => 'enviado_financiera_cheque']);

            $this->trackingService->registrarEvento(
                $ordenPago,
                'aprobacion_presupuesto',
                $estadoAnterior,
                'enviado_financiera_cheque',
                'Cheque aprobado por Presupuesto y enviado a Financiera'
            );

            DB::commit();

            $report = app(\App\Services\ReporteEnvioService::class)->generar(
                collect([$cheque]), 'enviado_financiera_cheque', 'cheque'
            );

            return redirect()->route('presupuesto.index')
                ->with('success', 'Cheque aprobado y enviado a Financiera exitosamente')
                ->with('download_report', $report);

        } catch (\Exception $e) {
            DB::rollback();
            return back()->with('error', 'Error al aprobar el cheque: ' . $e->getMessage());
        }
    }

    public function rechazar(Request $request, Cheque $cheque)
    {
        $request->validate([
            'motivo_rechazo' => 'required|string|min:10'
        ]);

        try {
            DB::beginTransaction();

            $ordenPago = $cheque->ordenPago;
            $estadoAnterior = $ordenPago->estado;
            $ordenPago->update([
                'estado' => 'rechazado_presupuesto',
                'observaciones' => $request->motivo_rechazo
            ]);

            $this->trackingService->registrarEvento(
                $ordenPago,
                'rechazo_presupuesto',
                $estadoAnterior,
                'rechazado_presupuesto',
                'Cheque rechazado por Presupuesto. Motivo: ' . $request->motivo_rechazo
            );

            DB::commit();

            return redirect()->route('presupuesto.index')
                ->with('success', 'Cheque rechazado exitosamente');

        } catch (\Exception $e) {
            DB::rollback();
            return back()->with('error', 'Error al rechazar el cheque: ' . $e->getMessage());
        }
    }

    public function aprobarMasivo(Request $request)
    {
        $ids = $request->input('cheques', []);
        if (empty($ids)) return back()->with('warning', 'No se seleccionaron cheques');

        try {
            DB::beginTransaction();
            $cheques = Cheque::whereIn('id', $ids)->get();
            $cont = 0;
            foreach ($cheques as $cheque) {
                $ordenPago = $cheque->ordenPago;
                if ($ordenPago->estado === 'enviado_presupuesto') {
                    $ordenPago->update(['estado' => 'enviado_financiera_cheque']);
                    $this->trackingService->registrarEvento(
                        $ordenPago,
                        'aprobacion_presupuesto',
                        'enviado_presupuesto',
                        'enviado_financiera_cheque',
                        'Cheque aprobado masivamente por Presupuesto y enviado a Financiera'
                    );
                    $cont++;
                }
            }
            DB::commit();
            $report = app(\App\Services\ReporteEnvioService::class)->generar(
                $cheques, 'enviado_financiera_cheque', 'cheque'
            );
            return back()->with('success', "Se han aprobado {$cont} cheques correctamente")
                ->with('download_report', $report);
        } catch (\Exception $e) {
            DB::rollback();
            return back()->with('error', 'Error al procesar el envío masivo: ' . $e->getMessage());
        }
    }
}