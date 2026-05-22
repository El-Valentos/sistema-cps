<?php

namespace App\Http\Controllers;

use App\Models\OrdenPago;
use App\Models\Cheque;
use App\Services\TrackingService;
use App\Services\PDFGeneratorService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ContabilidadController extends Controller
{
    public function __construct(
        protected TrackingService $trackingService,
        protected PDFGeneratorService $pdfService,
    ) {
        $this->middleware('auth');
    }

    public function index()
    {
        $ordenes = OrdenPago::where('estado', 'enviado_contabilidad')
            ->with(['beneficiario', 'categoriaGasto', 'creador'])
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('contabilidad.index', compact('ordenes'));
    }

    public function aprobar(OrdenPago $ordenPago)
    {
        try {
            DB::beginTransaction();

            // Generar cheque automáticamente (hilo seguro)
            $numeroCheque = DB::transaction(function () {
                $ultimoCheque = Cheque::whereYear('created_at', date('Y'))
                    ->lockForUpdate()
                    ->orderBy('id', 'desc')
                    ->first();
                
                $ultimoNumero = 0;
                if ($ultimoCheque && preg_match('/CH-\d{4}-(\d+)$/', $ultimoCheque->numero_cheque, $matches)) {
                    $ultimoNumero = intval($matches[1]);
                }
                
                return 'CH-' . date('Y') . '-' . str_pad($ultimoNumero + 1, 5, '0', STR_PAD_LEFT);
            });
            $cheque = Cheque::create([
                'orden_pago_id'       => $ordenPago->id,
                'numero_cheque'       => $numeroCheque,
                'gestion'             => date('Y'),
                'banco'               => 'A DESIGNAR',
                'fecha_emision'       => now()->toDateString(),
                'fecha_pago'          => now()->addDays(30)->toDateString(),
                'monto'               => $ordenPago->neto_pagar,
                'monto_literal'       => $this->pdfService->convertirNumeroALiteral($ordenPago->neto_pagar),
                'emitido_por'         => auth()->id(),
                'fecha_emision_sistema' => now(),
                'estado'              => 'emitido',
            ]);

            $ordenPago->update([
                'estado' => 'enviado_presupuesto',
                'aprobado_por' => auth()->id(),
                'fecha_aprobacion' => now(),
            ]);

            $this->trackingService->registrarEvento(
                $ordenPago,
                'aprobacion_contabilidad',
                'enviado_contabilidad',
                'enviado_presupuesto',
                'Orden aprobada por Contabilidad. Cheque generado: ' . $cheque->numero_cheque
            );

            DB::commit();

            return redirect()->route('contabilidad.index')
                ->with('success', 'Orden aprobada. Cheque ' . $cheque->numero_cheque . ' generado exitosamente');

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

        try {
            DB::beginTransaction();

            $ordenPago->update([
                'estado' => 'rechazado_contabilidad',
                'observaciones' => $request->motivo_rechazo
            ]);

            $this->trackingService->registrarEvento(
                $ordenPago,
                'rechazo_contabilidad',
                'enviado_contabilidad',
                'rechazado_contabilidad',
                'Orden rechazada por Contabilidad. Motivo: ' . $request->motivo_rechazo
            );

            DB::commit();

            return redirect()->route('contabilidad.index')
                ->with('success', 'Orden rechazada exitosamente');

        } catch (\Exception $e) {
            DB::rollback();
            return back()->with('error', 'Error al rechazar la orden: ' . $e->getMessage());
        }
    }

    public function verCheques()
    {
        $cheques = Cheque::whereHas('ordenPago', function ($query) {
                $query->whereIn('estado', [
                    'cheque_generado', 
                    'enviado_presupuesto', 
                    'rechazado_presupuesto', 
                    'enviado_financiera_cheque', 
                    'enviado_administracion',
                    'en_caja'
                ]);
            })
            ->with(['ordenPago.beneficiario'])
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('contabilidad.cheques', compact('cheques'));
    }

    public function showCheque(Cheque $cheque)
    {
        $cheque->load(['ordenPago.beneficiario', 'ordenPago.categoriaGasto']);
        return view('contabilidad.show', compact('cheque'));
    }

    public function enviarPresupuesto(Cheque $cheque)
    {
        try {
            DB::beginTransaction();

            $ordenPago = $cheque->ordenPago;
            $ordenPago->update(['estado' => 'enviado_presupuesto']);

            $this->trackingService->registrarEvento(
                $ordenPago,
                'envio_presupuesto',
                'cheque_generado',
                'enviado_presupuesto',
                'Cheque enviado a Presupuesto'
            );

            DB::commit();

            return back()->with('success', 'Cheque enviado a Presupuesto exitosamente');

        } catch (\Exception $e) {
            DB::rollback();
            return back()->with('error', 'Error al enviar el cheque: ' . $e->getMessage());
        }
    }

    public function editarCheque(Request $request, Cheque $cheque)
    {
        $request->validate([
            'monto' => 'required|numeric|min:0',
            'fecha_pago' => 'required|date',
            'observaciones' => 'nullable|string',
        ]);

        try {
            DB::beginTransaction();

            $cheque->update([
                'monto' => $request->monto,
                'fecha_pago' => $request->fecha_pago,
            ]);

            if ($request->observaciones) {
                $ordenPago = $cheque->ordenPago;
                $ordenPago->update(['observaciones' => $request->observaciones]);
            }

            DB::commit();

            return back()->with('success', 'Cheque actualizado exitosamente');

        } catch (\Exception $e) {
            DB::rollback();
            return back()->with('error', 'Error al editar el cheque: ' . $e->getMessage());
        }
    }

    public function anularCheque(Cheque $cheque)
    {
        try {
            DB::beginTransaction();

            $ordenPago = $cheque->ordenPago;
            $ordenPago->update(['estado' => 'anulado']);
            
            $cheque->update(['estado' => 'anulado']);

            $this->trackingService->registrarEvento(
                $ordenPago,
                'anulacion_cheque',
                $ordenPago->estado,
                'anulado',
                'Cheque anulado por Contabilidad'
            );

            DB::commit();

            return redirect()->route('contabilidad.cheques')
                ->with('success', 'Cheque anulado exitosamente');

        } catch (\Exception $e) {
            DB::rollback();
            return back()->with('error', 'Error al anular el cheque: ' . $e->getMessage());
        }
    }

    public function enviarAdministracion(Cheque $cheque)
    {
        try {
            DB::beginTransaction();

            $ordenPago = $cheque->ordenPago;
            $ordenPago->update(['estado' => 'enviado_administracion']);

            $this->trackingService->registrarEvento(
                $ordenPago,
                'envio_administracion',
                'cheque_generado',
                'enviado_administracion',
                'Cheque enviado a Administración directamente desde Contabilidad'
            );

            DB::commit();

            return redirect()->route('contabilidad.cheques')
                ->with('success', 'Cheque enviado a Administración exitosamente');

        } catch (\Exception $e) {
            DB::rollback();
            return back()->with('error', 'Error al enviar el cheque: ' . $e->getMessage());
        }
    }

    public function aprobarMasivo(Request $request)
    {
        $ids = $request->input('ordenes', []);
        if (empty($ids)) return back()->with('warning', 'No se seleccionaron órdenes');

        try {
            DB::beginTransaction();
            $ordenes = OrdenPago::whereIn('id', $ids)->where('estado', 'enviado_contabilidad')->get();
            $cont = 0;

            $ultimoCheque = Cheque::whereYear('created_at', date('Y'))
                ->lockForUpdate()
                ->orderBy('id', 'desc')
                ->first();

            $ultimoNumero = 0;
            if ($ultimoCheque && preg_match('/CH-\d{4}-(\d+)$/', $ultimoCheque->numero_cheque, $matches)) {
                $ultimoNumero = intval($matches[1]);
            }

            foreach ($ordenes as $orden) {
                $ultimoNumero++;
                $numeroCheque = 'CH-' . date('Y') . '-' . str_pad($ultimoNumero, 5, '0', STR_PAD_LEFT);
                $cheque = Cheque::create([
                    'orden_pago_id'       => $orden->id,
                    'numero_cheque'       => $numeroCheque,
                    'gestion'             => date('Y'),
                    'banco'               => 'A DESIGNAR',
                    'fecha_emision'       => now()->toDateString(),
                    'fecha_pago'          => now()->addDays(30)->toDateString(),
                    'monto'               => $orden->neto_pagar,
                    'monto_literal'       => $this->pdfService->convertirNumeroALiteral($orden->neto_pagar),
                    'emitido_por'         => auth()->id(),
                    'fecha_emision_sistema' => now(),
                    'estado'              => 'emitido',
                ]);

                $orden->update([
                    'estado' => 'enviado_presupuesto',
                    'aprobado_por' => auth()->id(),
                    'fecha_aprobacion' => now(),
                ]);

                $this->trackingService->registrarEvento(
                    $orden,
                    'aprobacion_contabilidad',
                    'enviado_contabilidad',
                    'enviado_presupuesto',
                    'Orden aprobada masivamente por Contabilidad. Cheque: ' . $cheque->numero_cheque
                );
                $cont++;
            }
            DB::commit();
            return back()->with('success', "Se han procesado {$cont} órdenes correctamente");
        } catch (\Exception $e) {
            DB::rollback();
            return back()->with('error', 'Error al procesar órdenes: ' . $e->getMessage());
        }
    }

    public function enviarAdministracionMasivo(Request $request)
    {
        $ids = $request->input('cheques', []);
        if (empty($ids)) return back()->with('warning', 'No se seleccionaron cheques');

        try {
            DB::beginTransaction();
            $cheques = Cheque::whereIn('id', $ids)->get();
            $cont = 0;
            foreach ($cheques as $cheque) {
                $ordenPago = $cheque->ordenPago;
                if ($ordenPago->estado !== 'enviado_administracion') {
                    $ordenPago->update(['estado' => 'enviado_administracion']);
                    $this->trackingService->registrarEvento(
                        $ordenPago,
                        'envio_administracion',
                        $ordenPago->estado,
                        'enviado_administracion',
                        'Cheque enviado masivamente a Administración desde Contabilidad'
                    );
                    $cont++;
                }
            }
            DB::commit();
            return back()->with('success', "Se han enviado {$cont} cheques a Administración");
        } catch (\Exception $e) {
            DB::rollback();
            return back()->with('error', 'Error al procesar cheques: ' . $e->getMessage());
        }
    }

    public function revisionCheques()
    {
        // Cheques enviados por Caja para auditoría en Contabilidad
        $ordenes = OrdenPago::where('estado', 'entregado_contabilidad')
            ->with(['beneficiario', 'cheque'])
            ->orderBy('updated_at', 'desc')
            ->paginate(15);

        return view('contabilidad.revision_cheques', compact('ordenes'));
    }

    public function enviarAArchivos(OrdenPago $ordenPago)
    {
        try {
            DB::beginTransaction();

            $ordenPago->update(['estado' => 'enviado_archivos']);

            $this->trackingService->registrarEvento(
                $ordenPago,
                'envio_archivos',
                $ordenPago->estado,
                'enviado_archivos',
                'Cheque auditado y enviado a Archivos para su custodia final'
            );

            DB::commit();

            return redirect()->route('contabilidad.revision_cheques')
                ->with('success', 'Orden enviada a Archivos correctamente');

        } catch (\Exception $e) {
            DB::rollback();
            return back()->with('error', 'Error al enviar a Archivos: ' . $e->getMessage());
        }
    }

    public function enviarAArchivosMasivo(Request $request)
    {
        $ids = $request->input('ordenes', []);
        if (empty($ids)) return back()->with('warning', 'No se seleccionaron órdenes');

        try {
            DB::beginTransaction();
            $ordenes = OrdenPago::whereIn('id', $ids)->where('estado', 'entregado_contabilidad')->get();
            $cont = 0;
            foreach ($ordenes as $orden) {
                $orden->update(['estado' => 'enviado_archivos']);
                $this->trackingService->registrarEvento(
                    $orden,
                    'envio_archivos',
                    $orden->estado,
                    'enviado_archivos',
                    'Envío masivo a Archivos después de auditoría en Contabilidad'
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