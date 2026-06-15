<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Beneficiario extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'beneficiarios';

    protected $attributes = [
        'activo' => true,
    ];

    protected $fillable = [
        'tipo',                    // persona / empresa
        'nombre_razon_social',
        'apellidos',
        'ci_nit',
        'direccion',
        'telefono',
        'email',
        'activo',
    ];

    protected $casts = [
        'activo' => 'boolean',
    ];

    // Relaciones
    public function ordenesPago()
    {
        return $this->hasMany(OrdenPago::class);
    }
}