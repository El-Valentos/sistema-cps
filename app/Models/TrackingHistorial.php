<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TrackingHistorial extends Model
{
    protected $table = 'tracking_historial';

    protected $fillable = [
        'orden_pago_id',
        'usuario_id',
        'area_origen_id',
        'area_destino_id',
        'accion',
        'estado_anterior',
        'estado_nuevo',
        'comentario',
        'metadata',
        'fecha_hora',
    ];

    protected $casts = [
        'fecha_hora' => 'datetime',
        'metadata' => 'array',
    ];

    public function ordenPago()
    {
        return $this->belongsTo(OrdenPago::class);
    }

    public function usuario()
    {
        return $this->belongsTo(User::class);
    }

    public function areaOrigen()
    {
        return $this->belongsTo(Area::class, 'area_origen_id');
    }

    public function areaDestino()
    {
        return $this->belongsTo(Area::class, 'area_destino_id');
    }
}