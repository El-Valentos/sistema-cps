<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CategoriaGasto extends Model
{
    use SoftDeletes;

    protected $table = 'categorias_gasto';

    protected $fillable = [
        'nombre',
        'codigo',
        'descripcion',
        'partida_presupuestaria',
        'presupuesto_anual',
        'requiere_aprobacion',
        'activo',
    ];

    protected $casts = [
        'activo' => 'boolean',
    ];

    public function ordenesPago()
    {
        return $this->hasMany(OrdenPago::class);
    }

    public function scopeActivas($query)
    {
        return $query->where('activo', true);
    }
}
