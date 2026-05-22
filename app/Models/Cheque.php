<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Cheque extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'orden_pago_id',
        'numero_cheque',
        'numero_cuenta',
        'banco',
        'fecha_emision',
        'monto',
        'monto_literal',
        'emitido_por',
        'fecha_emision_sistema',
        'gestion',
        'estado',
        'observaciones',
    ];

    protected $casts = [
        'monto' => 'decimal:2',
        'fecha_emision' => 'date',
        'fecha_pago' => 'date',
        'fecha_emision_sistema' => 'datetime',
    ];

    // Relaciones
    public function ordenPago()
    {
        return $this->belongsTo(OrdenPago::class);
    }

    public function emisor()
    {
        return $this->belongsTo(User::class, 'emitido_por');
    }
}