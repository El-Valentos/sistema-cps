<?php

namespace App\Http\Controllers;

use App\Models\OrdenPago;
use App\Models\Cheque;
use App\Services\TrackingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdministracionController extends Controller
{
    public function __construct(
        protected TrackingService $trackingService,
    ) {
        $this->middleware('auth');
    }

    public function index()
    {
        // Administración ve cheques aprobados por financiera
        $cheques = Cheque::whereHas('ordenPago', function ($query) {
                $query->where('estado', 'enviado_administracion');
            })
            ->with(['ordenPago.beneficiario', 'ordenPago.categoriaGasto'])
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('administracion.index', compact('cheques'));
    }

    public function show(Cheque $cheque)
    {
        $cheque->load(['ordenPago.beneficiario', 'ordenPago.categoriaGasto']);
        return view('administracion.show', compact('cheque'));
    }

    public function aprobar(Cheque $cheque)
    {
        try {
            DB::beginTransaction();

            $ordenPago = $cheque->ordenPago;
            $ordenPago->update(['estado' => 'en_caja']);

            $this->trackingService->registrarEvento(
                $ordenPago,
                'aprobacion_administracion',
                'enviado_administracion',
                'en_caja',
                'Cheque aprobado por Administración y enviado a Caja'
            );

            DB::commit();

            return redirect()->route('administracion.index')
                ->with('success', 'Cheque aprobado y enviado a Caja exitosamente');

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
            $ordenPago->update([
                'estado' => 'rechazado_administracion',
                'observaciones' => $request->motivo_rechazo
            ]);

            $this->trackingService->registrarEvento(
                $ordenPago,
                'rechazo_administracion',
                'enviado_administracion',
                'rechazado_administracion',
                'Cheque rechazado por Administración. Motivo: ' . $request->motivo_rechazo
            );

            DB::commit();

            return redirect()->route('administracion.index')
                ->with('success', 'Cheque rechazado exitosamente');

        } catch (\Exception $e) {
            DB::rollback();
            return back()->with('error', 'Error al rechazar the cheque: ' . $e->getMessage());
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
                if ($ordenPago->estado === 'enviado_administracion') {
                    $ordenPago->update(['estado' => 'en_caja']);
                    $this->trackingService->registrarEvento(
                        $ordenPago,
                        'aprobacion_administracion',
                        'enviado_administracion',
                        'en_caja',
                        'Cheque aprobado masivamente por Administración y enviado a Caja'
                    );
                    $cont++;
                }
            }
            DB::commit();
            return back()->with('success', "Se han aprobado {$cont} cheques correctamente");
        } catch (\Exception $e) {
            DB::rollback();
            return back()->with('error', 'Error al procesar el envío masivo: ' . $e->getMessage());
        }
    }
}