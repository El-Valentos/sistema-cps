<?php
// app/Models/Area.php - VERSIÓN FINAL DEFINITIVA
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Area extends Model
{
    use SoftDeletes;

    protected $table = 'areas';
    
    protected $fillable = [
        'nombre', 'codigo', 'descripcion', 'orden_flujo', 'activo'
    ];
    
    protected $casts = [
        'orden_flujo' => 'integer',
        'activo' => 'boolean'
    ];
    
    // Relaciones
    public function usuarios(): HasMany
    {
        return $this->hasMany(User::class);
    }
    
    public function trackingOrigen(): HasMany
    {
        return $this->hasMany(TrackingHistorial::class, 'area_origen_id');
    }
    
    public function trackingDestino(): HasMany
    {
        return $this->hasMany(TrackingHistorial::class, 'area_destino_id');
    }
    
    // Scopes
    public function scopeActivas($query)
    {
        return $query->where('activo', true);
    }
    
    public function scopeOrdenadas($query)
    {
        return $query->orderBy('orden_flujo');
    }
    
    // Atributo virtual (helper)
    public function getNombreCompletoAttribute(): string
    {
        return "{$this->nombre} ({$this->codigo})";
    }
}