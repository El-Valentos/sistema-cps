<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class User extends Authenticatable
{
    use Notifiable, HasRoles, HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'email',
        'password',
        'cargo',
        'telefono',
        'activo',
        'area_id',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password'          => 'hashed',
        'activo'            => 'boolean',
    ];

    // Scope para usuarios activos
    public function scopeActivos($query)
    {
        return $query->where('activo', true);
    }

    // Relaciones
    public function area()
    {
        return $this->belongsTo(Area::class);
    }

    public function ordenesCreadas()
    {
        return $this->hasMany(OrdenPago::class, 'creado_por');
    }

    public function chequesEmitidos()
    {
        return $this->hasMany(Cheque::class, 'emitido_por');
    }

    public function trackingHistorial()
    {
        return $this->hasMany(TrackingHistorial::class, 'usuario_id');
    }
}