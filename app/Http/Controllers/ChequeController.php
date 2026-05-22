<?php

namespace App\Http\Controllers;

use App\Models\{Cheque, OrdenPago};
use App\Http\Requests\ChequeRequest;
use App\Services\TrackingService;
use App\Services\PDFGeneratorService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;

class ChequeController extends Controller
{
    public function __construct(
        protected TrackingService $trackingService,
        protected PDFGeneratorService $pdfService,
    ) {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        $query = Cheque::with(['ordenPago.beneficiario', 'emisor']);

        if ($request->filled('estado')) {
            $query->where('estado', $request->estado);
        }

        if ($request->filled('fecha_desde')) {
            $query->whereDate('fecha_emision', '>=', $request->fecha_desde);
        }

        if ($request->filled('fecha_hasta')) {
            $query->whereDate('fecha_emision', '<=', $request->fecha_hasta);
        }

        $cheques = $query->orderBy('created_at', 'desc')->paginate(15);

        // Órdenes pendientes de generar cheque
        $ordenesPendientes = \App\Models\OrdenPago::with('beneficiario')
            ->where('estado', 'enviado_contabilidad')
            ->orderBy('created_at', 'desc')
            ->limit(20)
            ->get();

        return view('cheques.index', compact('cheques', 'ordenesPendientes'));
    }

    public function create(Request $request)
    {
        $ordenesPendientes = OrdenPago::where('estado', 'enviado_contabilidad')
            ->with('beneficiario')
            ->orderBy('created_at')
            ->get();

        $ordenPreseleccionada = null;
        if ($request->has('ordenPago')) {
            $ordenPreseleccionada = $ordenesPendientes->find($request->ordenPago);
        }

        if ($ordenesPendientes->isEmpty() && !$ordenPreseleccionada) {
            return redirect()->route('dashboard')
                ->with('warning', 'No hay órdenes de pago pendientes para generar cheques');
        }

        return view('cheques.create', compact('ordenesPendientes', 'ordenPreseleccionada'));
    }

    public function store(ChequeRequest $request)
    {
        try {
            DB::beginTransaction();

            $ordenPago = OrdenPago::findOrFail($request->orden_pago_id);

            if ($ordenPago->estado !== 'enviado_contabilidad') {
                throw new \Exception('La orden de pago no está en estado para generar cheque');
            }

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
                'orden_pago_id' => $ordenPago->id,
                'numero_cheque' => $numeroCheque,
                'gestion' => date('Y'),
                'banco' => $request->banco,
                'numero_cuenta' => $request->numero_cuenta,
                'fecha_emision' => $request->fecha_emision,
                'monto' => $ordenPago->neto_pagar,
                'monto_literal' => $this->pdfService->convertirNumeroALiteral($ordenPago->neto_pagar),
                'emitido_por' => auth()->id(),
                'fecha_emision_sistema' => now(),
                'estado' => 'emitido',
                'observaciones' => $request->observaciones
            ]);

            $ordenPago->update(['estado' => 'enviado_presupuesto']);

            $this->trackingService->registrarEvento(
                $ordenPago,
                'generacion_cheque',
                'enviado_contabilidad',
                'enviado_presupuesto',
                "Cheque N° {$numeroCheque} generado y enviado a Presupuesto",
                ['cheque_id' => $cheque->id, 'numero_cheque' => $numeroCheque]
            );

            DB::commit();

            return redirect()->route('cheques.show', $cheque)
                ->with('success', 'Cheque generado. Verifique los datos antes de confirmar.');

        } catch (\Exception $e) {
            DB::rollback();
            return back()->with('error', 'Error al generar cheque: ' . $e->getMessage());
        }
    }

    public function show(Cheque $cheque)
    {
        $cheque->load(['ordenPago.beneficiario', 'ordenPago.liquidador', 'emisor']);

        return view('cheques.show', compact('cheque'));
    }

    public function edit(Cheque $cheque)
    {
        $cheque->load(['ordenPago.beneficiario', 'ordenPago.liquidador']);

        return view('cheques.edit', compact('cheque'));
    }

    public function update(Request $request, Cheque $cheque)
    {
        $request->validate([
            'banco' => 'required|string|max:100',
            'numero_cuenta' => 'nullable|string|max:50',
            'fecha_emision' => 'required|date',
        ]);

        $cheque->update([
            'banco' => $request->banco,
            'numero_cuenta' => $request->numero_cuenta,
            'fecha_emision' => $request->fecha_emision,
        ]);

        return redirect()->route('cheques.show', $cheque)
            ->with('success', 'Cheque actualizado correctamente');
    }

    public function confirmar(Cheque $cheque)
    {
        try {
            DB::beginTransaction();

            $cheque->update(['estado' => 'impreso']);

            $ordenPago = $cheque->ordenPago;
            $ordenPago->update(['estado' => 'enviado_presupuesto']);

            $this->trackingService->registrarEvento(
                $ordenPago,
                'confirmacion',
                'emitido',
                'enviado_presupuesto',
                "Cheque N° {$cheque->numero_cheque} confirmado y enviado a Presupuesto",
                ['cheque_id' => $cheque->id]
            );

            DB::commit();

            return redirect()->route('presupuesto.index')
                ->with('success', 'Cheque confirmado y enviado a Presupuesto');

        } catch (\Exception $e) {
            DB::rollback();
            return back()->with('error', 'Error al confirmar cheque');
        }
    }

    public function enviarMasivo(Request $request)
    {
        $ids = $request->input('cheques', []);
        
        if (empty($ids)) {
            return back()->with('warning', 'No se seleccionaron cheques para enviar');
        }

        try {
            DB::beginTransaction();
            
            $cheques = Cheque::whereIn('id', $ids)
                ->where('estado', 'emitido')
                ->get();
            
            $cont = 0;
            foreach ($cheques as $cheque) {
                $cheque->update(['estado' => 'impreso']);

                $ordenPago = $cheque->ordenPago;
                $ordenPago->update(['estado' => 'enviado_presupuesto']);

                $this->trackingService->registrarEvento(
                    $ordenPago,
                    'confirmacion',
                    'emitido',
                    'enviado_presupuesto',
                    "Cheque N° {$cheque->numero_cheque} confirmado masivamente y enviado a Presupuesto",
                    ['cheque_id' => $cheque->id]
                );
                $cont++;
            }

            DB::commit();

            return back()->with('success', "Se han enviado {$cont} cheques a Presupuesto correctamente");

        } catch (\Exception $e) {
            DB::rollback();
            return back()->with('error', 'Error al procesar el envío masivo: ' . $e->getMessage());
        }
    }

    public function buscar()
    {
        return view('cheques.buscar');
    }

    public function buscarPost(Request $request)
    {
        $query = Cheque::with(['ordenPago.beneficiario']);

        if ($request->filled('numero_cheque')) {
            $query->where('numero_cheque', 'like', '%' . $request->numero_cheque . '%');
        }

        if ($request->filled('nombre_beneficiario')) {
            $nombre = $request->nombre_beneficiario;
            $query->whereHas('ordenPago.beneficiario', function($q) use ($nombre) {
                $q->where('nombre_razon_social', 'like', '%' . $nombre . '%')
                  ->orWhere('apellidos', 'like', '%' . $nombre . '%');
            });
        }

        if ($request->filled('ci_nit')) {
            $ci = $request->ci_nit;
            $query->whereHas('ordenPago.beneficiario', function($q) use ($ci) {
                $q->where('ci_nit', 'like', '%' . $ci . '%');
            });
        }

        if ($request->filled('monto_desde')) {
            $query->where('monto', '>=', $request->monto_desde);
        }

        if ($request->filled('monto_hasta')) {
            $query->where('monto', '<=', $request->monto_hasta);
        }

        $cheques = $query->orderBy('created_at', 'desc')->limit(50)->get();

        return view('cheques.buscar', compact('cheques'));
    }

    public function imprimir(Cheque $cheque)
    {
        $this->authorize('imprimir', $cheque);

        if ($cheque->estado === 'emitido') {
            $cheque->update(['estado' => 'impreso']);
        }

        return redirect()->route('cheques.pdf', $cheque);
    }

    public function generarPDF(Cheque $cheque)
    {
        $cheque->load(['ordenPago.beneficiario', 'ordenPago.liquidador']);

        $pdf = PDF::loadView('dpfs.cheque', compact('cheque'));
        $pdf->setPaper('letter', 'portrait');

        return $pdf->stream("Cheque_{$cheque->numero_cheque}.pdf");
    }

    public function anular(Cheque $cheque, Request $request)
    {
        $this->authorize('anular', $cheque);

        try {
            DB::beginTransaction();

            $request->validate([
                'motivo_anulacion' => 'required|string|min:10'
            ]);

            $cheque->update([
                'estado' => 'anulado',
                'observaciones' => $request->motivo_anulacion
            ]);

            $ordenPago = $cheque->ordenPago;
            $ordenPago->update(['estado' => 'enviado_contabilidad']);

            $this->trackingService->registrarEvento(
                $ordenPago,
                'rechazo',
                'cheque_generado',
                'enviado_contabilidad',
                "Cheque anulado. Motivo: {$request->motivo_anulacion}"
            );

            DB::commit();

            return redirect()->route('cheques.index')
                ->with('success', 'Cheque anulado exitosamente');

        } catch (\Exception $e) {
            DB::rollback();
            return back()->with('error', 'Error al anular cheque');
        }
    }

}