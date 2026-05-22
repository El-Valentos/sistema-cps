<?php

namespace App\Services;

use App\Models\OrdenPago;
use Illuminate\Support\Facades\DB;

class WorkflowOrchestratorService
{
    private array $transitions = [
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
        'entregado' => ['cerrado'],
    ];

    private array $accionMap = [
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
        'cerrado' => 'cierre',
    ];

    public function validarTransicion(string $from, string $to): bool
    {
        return isset($this->transitions[$from]) && in_array($to, $this->transitions[$from]);
    }

    public function getAccion(string $estado): string
    {
        return $this->accionMap[$estado] ?? 'observacion';
    }

    public function getEstadosDisponibles(string $estadoActual, string $role): array
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
            $estados[] = ['value' => 'rechazado_financiera', 'label' => 'Rechazar Cheque'];
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

    public function transicionar(
        OrdenPago $orden,
        string $nuevoEstado,
        string $comentario = null,
        array $extra = [],
    ): array {
        if (!$this->validarTransicion($orden->estado, $nuevoEstado)) {
            throw new \InvalidArgumentException(
                "Transición inválida: {$orden->estado} → {$nuevoEstado}"
            );
        }

        try {
            DB::beginTransaction();

            $estadoAnterior = $orden->estado;

            $data = array_merge(['estado' => $nuevoEstado], $extra);
            $orden->update($data);

            if ($nuevoEstado === 'entregado' && !isset($extra['fecha_cierre'])) {
                $orden->update(['fecha_cierre' => now()]);
            }

            app(TrackingService::class)->registrarEvento(
                $orden,
                $this->getAccion($nuevoEstado),
                $estadoAnterior,
                $nuevoEstado,
                $comentario,
                ['actualizado_por' => auth()->user()->name ?? 'sistema']
            );

            DB::commit();

            return ['success' => true, 'message' => 'Estado actualizado exitosamente'];
        } catch (\Exception $e) {
            DB::rollback();
            throw $e;
        }
    }
}
