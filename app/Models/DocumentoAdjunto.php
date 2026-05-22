<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DocumentoAdjunto extends Model
{
    protected $table = 'documentos_adjuntos';

    protected $fillable = [
        'orden_pago_id',
        'nombre_archivo',
        'ruta_archivo',
        'tipo_archivo',
        'tamano',
        'subido_por',
    ];

    public function ordenPago()
    {
        return $this->belongsTo(OrdenPago::class);
    }

    public function subidoPor()
    {
        return $this->belongsTo(User::class, 'subido_por');
    }
}