<?php

namespace App\Services;

use App\Models\{OrdenPago, TrackingHistorial, Area};

class TrackingService
{
    public function registrarEvento(
        OrdenPago $orden,
        string $accion,
        ?string $estadoAnterior,
        string $estadoNuevo,
        ?string $comentario = null,
        array $metadata = [],
        ?int $usuarioId = null
    ) {
        $areaOrigen = $this->getAreaOrigen($estadoAnterior);
        $areaDestino = $this->getAreaDestino($estadoNuevo);
        $userId = $usuarioId ?? (auth()->id() ?? null);

        return TrackingHistorial::create([
            'orden_pago_id'    => $orden->id,
            'area_origen_id'   => $areaOrigen?->id,
            'area_destino_id'  => $areaDestino?->id,
            'usuario_id'       => $userId,
            'accion'           => $accion,
            'estado_anterior'  => $estadoAnterior,
            'estado_nuevo'     => $estadoNuevo,
            'comentario'       => $comentario,
            'metadata'         => $metadata,
            'fecha_hora'       => now(),
        ]);
    }

    private function getAreaOrigen(?string $estado): ?Area
    {
        if (!$estado) return null;
        
        $mapa = [
            'pendiente_tesoreria'        => 'Tesorería',
            'enviado_financiera'         => 'Tesorería',
            'reenviado_financiera'       => 'Tesorería',
            'rechazado_financiera'       => 'Financiera',
            'enviado_contabilidad'       => 'Financiera',
            'rechazado_contabilidad'     => 'Contabilidad',
            'cheque_generado'            => 'Contabilidad',
            'enviado_presupuesto'        => 'Contabilidad',
            'rechazado_presupuesto'      => 'Presupuesto',
            'enviado_financiera_cheque'  => 'Presupuesto',
            'rechazado_financiera_cheque'=> 'Financiera',
            'enviado_administracion'     => 'Financiera',
            'rechazado_administracion'   => 'Administración',
            'en_caja'                    => 'Administración',
            'entregado'                  => 'Caja',
            'cobrado'                    => 'Caja',
            'revalidando'                => 'Caja',
            'revalidado'                 => 'Caja',
        ];

        $areaNombre = $mapa[$estado] ?? null;
        return $areaNombre ? Area::where('nombre', $areaNombre)->first() : null;
    }

    private function getAreaDestino(string $estado): ?Area
    {
        $mapa = [
            'pendiente_tesoreria'        => 'Tesorería',
            'enviado_financiera'         => 'Financiera',
            'reenviado_financiera'       => 'Financiera',
            'rechazado_financiera'       => 'Tesorería',
            'enviado_contabilidad'       => 'Contabilidad',
            'rechazado_contabilidad'     => 'Tesorería',
            'cheque_generado'            => 'Contabilidad',
            'enviado_presupuesto'        => 'Presupuesto',
            'rechazado_presupuesto'      => 'Contabilidad',
            'enviado_financiera_cheque'  => 'Financiera',
            'rechazado_financiera_cheque'=> 'Contabilidad',
            'enviado_administracion'     => 'Administración',
            'rechazado_administracion'   => 'Financiera',
            'en_caja'                    => 'Caja',
            'entregado'                  => null,
            'cobrado'                    => null,
            'revalidando'                => null,
            'revalidado'                 => null,
            'cerrado'                    => null,
        ];

        $areaNombre = $mapa[$estado] ?? null;
        return $areaNombre ? Area::where('nombre', $areaNombre)->first() : null;
    }

    public function obtenerTrackingCompleto(OrdenPago $orden)
    {
        return $orden->trackingHistorial()
            ->with(['usuario', 'areaOrigen', 'areaDestino'])
            ->orderBy('fecha_hora', 'asc')
            ->get();
    }

    public function obtenerUltimoMovimiento(OrdenPago $orden)
    {
        return $orden->trackingHistorial()
            ->with(['usuario'])
            ->orderBy('fecha_hora', 'desc')
            ->first();
    }
}