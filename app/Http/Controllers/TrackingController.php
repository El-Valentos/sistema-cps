<?php

namespace App\Http\Controllers;

use App\Models\OrdenPago;
use App\Models\TrackingHistorial;
use App\Services\TrackingService;
use App\Services\WorkflowOrchestratorService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TrackingController extends Controller
{
    public function __construct(
        protected TrackingService $trackingService,
        protected WorkflowOrchestratorService $workflowOrchestrator,
    ) {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        $ordenes = $this->getFilteredQuery($request)->orderBy('created_at', 'desc')->paginate(15);

        return view('tracking.index', compact('ordenes'));
    }

    public function generarPDF(Request $request)
    {
        $ordenes = $this->getFilteredQuery($request)->orderBy('created_at', 'desc')->get();

        $pdf = Pdf::loadView('dpfs.tracking', compact('ordenes'));
        $pdf->setPaper('letter', 'landscape');

        return $pdf->download('Reporte_Tracking_' . now()->format('Y-m-d_Hi') . '.pdf');
    }

    public function show(OrdenPago $ordenPago)
    {
        $ordenPago->load([
            'beneficiario', 
            'categoriaGasto', 
            'cheque',
            'trackingHistorial' => function($q) {
                $q->with(['usuario', 'areaOrigen', 'areaDestino'])->orderBy('fecha_hora', 'desc');
            },
            'documentosAdjuntos'
        ]);

        // Verificar si el usuario puede ver este tracking
        $userRole = auth()->user()->roles->first()->name ?? '';
        if (!in_array($userRole, ['Super Admin', 'Archivos']) && 
            !$this->usuarioPuedeVerOrden($ordenPago, $userRole)) {
            abort(403, 'No tiene permiso para ver este tracking');
        }

        $tracking = $ordenPago->trackingHistorial;
        $estadosDisponibles = $this->getEstadosDisponibles($ordenPago->estado, $userRole);

        return view('tracking.show', compact('ordenPago', 'tracking', 'estadosDisponibles'));
    }

    public function actualizar(Request $request, OrdenPago $ordenPago)
    {
        $this->authorize('actualizar', $ordenPago);

        $request->validate([
            'nuevo_estado' => 'required|in:enviado_financiera,rechazado_financiera,enviado_contabilidad,rechazado_contabilidad,enviado_presupuesto,rechazado_presupuesto,enviado_financiera_cheque,rechazado_financiera,enviado_administracion,rechazado_administracion,en_caja,entregado,cerrado',
            'comentario' => 'nullable|string|max:500'
        ]);

        try {
            DB::beginTransaction();

            $estadoAnterior = $ordenPago->estado;
            $nuevoEstado = $request->nuevo_estado;

            if (!$this->validarTransicion($estadoAnterior, $nuevoEstado)) {
                throw new \Exception('Transición de estado no válida');
            }

            $ordenPago->update(['estado' => $nuevoEstado]);

            if ($nuevoEstado === 'entregado') {
                $ordenPago->update(['fecha_cierre' => now()]);
            }

            $accion = $this->getAccionPorEstado($nuevoEstado);
            $this->trackingService->registrarEvento(
                $ordenPago,
                $accion,
                $estadoAnterior,
                $nuevoEstado,
                $request->comentario,
                ['actualizado_por' => auth()->user()->name]
            );

            DB::commit();

            return redirect()->route('tracking.show', $ordenPago)
                ->with('success', 'Estado actualizado exitosamente');

        } catch (\Exception $e) {
            DB::rollback();
            return back()->with('error', 'Error al actualizar estado: ' . $e->getMessage());
        }
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
                'cheque_generado',
                'entregado',
                "Cheque entregado a: {$request->recibido_por} (CI: {$request->ci_recibido})",
                [
                    'recibido_por' => $request->recibido_por,
                    'ci_recibido' => $request->ci_recibido,
                    'fecha_entrega' => $request->fecha_entrega
                ]
            );

            DB::commit();

            return redirect()->route('tracking.show', $ordenPago)
                ->with('success', 'Entrega registrada exitosamente');

        } catch (\Exception $e) {
            DB::rollback();
            return back()->with('error', 'Error al registrar entrega');
        }
    }

    public function cerrar(OrdenPago $ordenPago)
    {
        $this->authorize('cerrar', $ordenPago);

        try {
            DB::beginTransaction();

            if ($ordenPago->estado !== 'entregado') {
                throw new \Exception('Solo se pueden cerrar órdenes entregadas');
            }

            $ordenPago->update(['estado' => 'cerrado']);

            $this->trackingService->registrarEvento(
                $ordenPago,
                'cierre',
                'entregado',
                'cerrado',
                'Trámite cerrado exitosamente'
            );

            DB::commit();

            return redirect()->route('tracking.show', $ordenPago)
                ->with('success', 'Trámite cerrado exitosamente');

        } catch (\Exception $e) {
            DB::rollback();
            return back()->with('error', 'Error al cerrar trámite');
        }
    }

    public function notificaciones(OrdenPago $ordenPago)
    {
        $notificaciones = $ordenPago->trackingHistorial()
            ->with('usuario')
            ->where('created_at', '>=', now()->subHours(24))
            ->latest()
            ->get()
            ->map(function($item) {
                return [
                    'id' => $item->id,
                    'titulo' => $this->getTituloNotificacion($item->accion),
                    'mensaje' => $item->comentario ?? $this->getMensajeNotificacion($item->accion),
                    'fecha' => $item->created_at->diffForHumans(),
                    'tipo' => $this->getTipoNotificacion($item->accion),
                    'usuario' => $item->usuario->name ?? 'Sistema'
                ];
            });

        return response()->json($notificaciones);
    }

    // ==================== MÉTODOS PRIVADOS ====================

    private function getFilteredQuery(Request $request)
    {
        $query = OrdenPago::with(['beneficiario', 'trackingHistorial' => function($q) {
            $q->latest()->limit(1);
        }]);

        if ($request->filled('estado')) {
            $query->where('estado', $request->estado);
        }

        if ($request->filled('numero_orden')) {
            $query->where('numero_orden', 'like', "%{$request->numero_orden}%");
        }

        if ($request->filled('fecha_desde')) {
            $query->whereDate('created_at', '>=', $request->fecha_desde);
        }

        if ($request->filled('fecha_hasta')) {
            $query->whereDate('created_at', '<=', $request->fecha_hasta);
        }

        $userRole = auth()->user()->roles->first()->name ?? '';
        if ($userRole === 'Caja') {
            $query->whereIn('estado', ['en_caja']);
        } elseif ($userRole === 'Contabilidad') {
            $query->whereIn('estado', ['enviado_contabilidad', 'rechazado_contabilidad']);
        } elseif ($userRole === 'Presupuesto') {
            $query->whereIn('estado', ['enviado_presupuesto', 'rechazado_presupuesto']);
        } elseif ($userRole === 'Financiera') {
            $query->whereIn('estado', ['enviado_financiera', 'enviado_financiera_cheque', 'rechazado_financiera']);
        } elseif ($userRole === 'Administración') {
            $query->whereIn('estado', ['enviado_administracion', 'rechazado_administracion']);
        } elseif ($userRole === 'Tesorería') {
            $query->whereIn('estado', ['pendiente_tesoreria', 'rechazado_financiera']);
        }

        return $query;
    }

    private function usuarioPuedeVerOrden(OrdenPago $orden, string $role): bool
    {
        switch($role) {
            case 'Tesorería':
                return in_array($orden->estado, ['pendiente_tesoreria', 'rechazado_financiera']) || $orden->creado_por === auth()->id();
            case 'Financiera':
                return in_array($orden->estado, ['enviado_financiera', 'rechazado_financiera', 'enviado_contabilidad']);
            case 'Contabilidad':
                return in_array($orden->estado, ['enviado_contabilidad', 'cheque_generado']);
            case 'Caja':
                return in_array($orden->estado, ['cheque_generado', 'en_caja', 'entregado']);
            default:
                return true;
        }
    }

    private function validarTransicion(string $from, string $to): bool
    {
        $transiciones = [
            'pendiente_tesoreria' => ['enviado_financiera'],
            'enviado_financiera' => ['enviado_contabilidad', 'rechazado_financiera'],
            'rechazado_financiera' => ['enviado_financiera'],
            'enviado_contabilidad' => ['enviado_presupuesto', 'rechazado_contabilidad'],
            'rechazado_contabilidad' => ['enviado_contabilidad'],
            'enviado_presupuesto' => ['enviado_financiera_cheque', 'rechazado_presupuesto'],
            'rechazado_presupuesto' => ['enviado_presupuesto'],
            'enviado_financiera_cheque' => ['enviado_administracion', 'rechazado_financiera'],
            'enviado_administracion' => ['en_caja', 'rechazado_administracion'],
            'rechazado_administracion' => ['enviado_administracion'],
            'en_caja' => ['entregado'],
            'entregado' => ['cerrado']
        ];

        return isset($transiciones[$from]) && in_array($to, $transiciones[$from]);
    }

    private function getAccionPorEstado(string $estado): string
    {
        $mapa = [
            'enviado_financiera' => 'envio_financiera',
            'rechazado_financiera' => 'rechazo_financiera',
            'enviado_contabilidad' => 'envio_contabilidad',
            'rechazado_contabilidad' => 'rechazo_contabilidad',
            'enviado_presupuesto' => 'envio_presupuesto',
            'rechazado_presupuesto' => 'rechazo_presupuesto',
            'enviado_financiera_cheque' => 'revision_financiera_cheque',
            'enviado_administracion' => 'envio_administracion',
            'rechazado_administracion' => 'rechazo_administracion',
            'en_caja' => 'envio_caja',
            'entregado' => 'entrega',
            'cerrado' => 'cierre'
        ];

        return $mapa[$estado] ?? 'observacion';
    }

    private function getEstadosDisponibles(string $estadoActual, string $role): array
    {
        $estados = [];

        if ($role === 'Tesorería' && in_array($estadoActual, ['pendiente_tesoreria', 'rechazado_financiera'])) {
            $estados[] = ['value' => 'enviado_financiera', 'label' => 'Enviar a Financiera'];
        }

        if ($role === 'Financiera' && $estadoActual === 'enviado_financiera') {
            $estados[] = ['value' => 'enviado_contabilidad', 'label' => 'Aprobar a Contabilidad'];
            $estados[] = ['value' => 'rechazado_financiera', 'label' => 'Rechazar Orden'];
        }

        if ($role === 'Contabilidad' && $estadoActual === 'enviado_contabilidad') {
            $estados[] = ['value' => 'enviado_presupuesto', 'label' => 'Enviar a Presupuesto'];
        }

        if ($role === 'Contabilidad' && $estadoActual === 'rechazado_contabilidad') {
            $estados[] = ['value' => 'enviado_contabilidad', 'label' => 'Reenviar a Contabilidad'];
        }

        if ($role === 'Presupuesto' && $estadoActual === 'enviado_presupuesto') {
            $estados[] = ['value' => 'enviado_financiera_cheque', 'label' => 'Aprobar y enviar a Financiera'];
            $estados[] = ['value' => 'rechazado_presupuesto', 'label' => 'Rechazar Orden'];
        }

        if ($role === 'Financiera' && $estadoActual === 'enviado_financiera_cheque') {
            $estados[] = ['value' => 'enviado_administracion', 'label' => 'Aprobar a Administración'];
            $estados[] = ['value' => 'rechazado_financiera', 'label' => 'RechazarCheque'];
        }

        if ($role === 'Administración' && $estadoActual === 'enviado_administracion') {
            $estados[] = ['value' => 'en_caja', 'label' => 'Aprobar y enviar a Caja'];
            $estados[] = ['value' => 'rechazado_administracion', 'label' => 'Rechazar Orden'];
        }

        if ($role === 'Caja' && in_array($estadoActual, ['en_caja'])) {
            $estados[] = ['value' => 'entregado', 'label' => 'Registrar Entrega'];
        }

        if ($role === 'Super Admin' && $estadoActual === 'entregado') {
            $estados[] = ['value' => 'cerrado', 'label' => 'Cerrar Trámite'];
        }

        return $estados;
    }

    private function getTituloNotificacion(string $accion): string
    {
        return [
            'creacion' => 'Nueva Orden de Pago',
            'envio_financiera' => 'Orden Enviada a Financiera',
            'rechazo_financiera' => 'Orden Rechazada por Financiera',
            'envio_contabilidad' => 'Orden Enviada a Contabilidad',
            'generacion_cheque' => 'Cheque Generado',
            'envio_caja' => 'Enviado a Caja',
            'entrega' => 'Cheque Entregado',
            'cierre' => 'Trámite Cerrado'
        ][$accion] ?? 'Actualización de Estado';
    }

    private function getMensajeNotificacion(string $accion): string
    {
        return [
            'creacion' => 'Se ha creado una nueva orden de pago',
            'envio_financiera' => 'La orden ha sido enviada a financiera',
            'rechazo_financiera' => 'La orden ha sido rechazada por financiera',
            'envio_contabilidad' => 'La orden ha sido enviada a contabilidad',
            'generacion_cheque' => 'Se ha generado el cheque correspondiente',
            'envio_caja' => 'El cheque ha sido enviado a caja',
            'entrega' => 'Se ha registrado la entrega del cheque',
            'cierre' => 'El trámite ha sido cerrado'
        ][$accion] ?? 'El estado del trámite ha sido actualizado';
    }

    private function getTipoNotificacion(string $accion): string
    {
        return [
            'creacion' => 'info',
            'envio_financiera' => 'warning',
            'rechazo_financiera' => 'error',
            'envio_contabilidad' => 'warning',
            'generacion_cheque' => 'success',
            'entrega' => 'success',
            'cierre' => 'info'
        ][$accion] ?? 'info';
    }
}