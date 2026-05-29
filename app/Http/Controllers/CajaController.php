<?php

namespace App\Http\Controllers;

use App\Models\OrdenPago;
use App\Services\TrackingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CajaController extends Controller
{
    public function __construct(
        protected TrackingService $trackingService,
    ) {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        $query = OrdenPago::with(['beneficiario', 'cheque'])
            ->whereIn('estado', ['en_caja', 'entregado', 'revalidado']);

        if ($request->filled('estado')) {
            $query->where('estado', $request->estado);
        }

        if ($request->filled('numero_orden')) {
            $query->where('numero_orden', 'like', "%{$request->numero_orden}%");
        }

        $ordenes = $query->orderBy('updated_at', 'desc')->paginate(15);

        return view('caja.index', compact('ordenes'));
    }

    public function show(OrdenPago $ordenPago)
    {
        if (!in_array($ordenPago->estado, ['en_caja', 'entregado', 'revalidado'])) {
            abort(403, 'Esta orden no está disponible para Caja');
        }

        $ordenPago->load(['beneficiario', 'cheque', 'trackingHistorial.usuario']);
        
        return view('caja.show', compact('ordenPago'));
    }

    public function registrarEntrega(Request $request, OrdenPago $ordenPago)
    {
        $this->authorize('entregar', $ordenPago);

        $request->validate([
            'recibido_por' => 'required|string|max:200',
            'ci_recibido' => 'required|string|max:20',
            'fecha_entrega' => 'required|date',
            'observaciones' => 'nullable|string'
        ]);

        try {
            DB::beginTransaction();

            $ordenPago->update([
                'estado' => 'entregado',
                'fecha_cierre' => $request->fecha_entrega,
                'observaciones' => $request->observaciones
            ]);

            $this->trackingService->registrarEvento(
                $ordenPago,
                'entrega',
                $ordenPago->estado, // usually 'en_caja' or 'cheque_generado'
                'entregado',
                "Cheque entregado a: {$request->recibido_por} (CI: {$request->ci_recibido})",
                [
                    'recibido_por' => $request->recibido_por,
                    'ci_recibido' => $request->ci_recibido,
                    'fecha_entrega' => $request->fecha_entrega
                ]
            );

            DB::commit();

            return redirect()->route('caja.index')
                ->with('success', 'Entrega de cheque registrada exitosamente');

        } catch (\Exception $e) {
            DB::rollback();
            return back()->with('error', 'Error al registrar entrega: ' . $e->getMessage());
        }
    }

    public function enviarContabilidad(OrdenPago $ordenPago)
    {
        try {
            DB::beginTransaction();

            $ordenPago->update(['estado' => 'entregado_contabilidad']);

            $this->trackingService->registrarEvento(
                $ordenPago,
                'envio_contabilidad_final',
                'entregado',
                'entregado_contabilidad',
                'Orden de pago entregada enviada a Contabilidad para su archivo'
            );

            DB::commit();

            return redirect()->route('caja.index')
                ->with('success', 'Orden enviada a Contabilidad para archivo');

        } catch (\Exception $e) {
            DB::rollback();
            return back()->with('error', 'Error al enviar a Contabilidad: ' . $e->getMessage());
        }
    }

    public function marcarCobrado(OrdenPago $ordenPago)
    {
        if ($ordenPago->estado !== 'entregado') {
            return back()->with('error', 'Solo se pueden marcar como cobradas órdenes entregadas.');
        }

        try {
            DB::beginTransaction();

            $ordenPago->update(['estado' => 'cobrado']);

            $this->trackingService->registrarEvento(
                $ordenPago,
                'cobrado',
                'entregado',
                'cobrado',
                'Cheque marcado como cobrado por Caja',
            );

            DB::commit();

            return back()->with('success', 'Cheque marcado como cobrado exitosamente.');
        } catch (\Exception $e) {
            DB::rollback();
            return back()->with('error', 'Error al marcar como cobrado: ' . $e->getMessage());
        }
    }

    public function revalidar(OrdenPago $ordenPago)
    {
        if ($ordenPago->estado !== 'entregado') {
            return back()->with('error', 'Solo se pueden revalidar órdenes entregadas.');
        }

        try {
            DB::beginTransaction();

            $ordenPago->update(['estado' => 'revalidado']);

            $this->trackingService->registrarEvento(
                $ordenPago,
                'revalidar',
                'entregado',
                'revalidado',
                'Cheque revalidado por Caja',
            );

            DB::commit();

            return back()->with('success', 'Cheque revalidado exitosamente.');
        } catch (\Exception $e) {
            DB::rollback();
            return back()->with('error', 'Error al revalidar cheque: ' . $e->getMessage());
        }
    }

    public function revalidarMasivo(Request $request)
    {
        $ids = $request->input('ordenes', []);
        if (empty($ids)) return back()->with('warning', 'No se seleccionaron órdenes');

        try {
            DB::beginTransaction();
            $ordenes = OrdenPago::whereIn('id', $ids)->where('estado', 'entregado')->get();
            $cont = 0;
            foreach ($ordenes as $orden) {
                $orden->update(['estado' => 'revalidado']);
                $this->trackingService->registrarEvento(
                    $orden,
                    'revalidar',
                    'entregado',
                    'revalidado',
                    'Cheque revalidado masivamente por Caja'
                );
                $cont++;
            }
            DB::commit();
            return back()->with('success', "Se han revalidado {$cont} cheques correctamente");
        } catch (\Exception $e) {
            DB::rollback();
            return back()->with('error', 'Error al procesar la revalidación masiva: ' . $e->getMessage());
        }
    }

    public function enviarContabilidadMasivo(Request $request)
    {
        $ids = $request->input('ordenes', []);
        if (empty($ids)) return back()->with('warning', 'No se seleccionaron órdenes');

        try {
            DB::beginTransaction();
            $ordenes = OrdenPago::whereIn('id', $ids)->where('estado', 'entregado')->get();
            $cont = 0;
            foreach ($ordenes as $orden) {
                $orden->update(['estado' => 'entregado_contabilidad']);
                $this->trackingService->registrarEvento(
                    $orden,
                    'envio_contabilidad_final',
                    'entregado',
                    'entregado_contabilidad',
                    'Orden de pago entregada enviada masivamente a Archivos'
                );
                $cont++;
            }
            DB::commit();
            return back()->with('success', "Se han enviado {$cont} órdenes a Archivos correctamente");
        } catch (\Exception $e) {
            DB::rollback();
            return back()->with('error', 'Error al procesar el envío masivo: ' . $e->getMessage());
        }
    }
}
