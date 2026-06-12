<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class OrdenPago extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'ordenes_pago';

    protected $attributes = [
        'numero_fojas' => 0,
        'tiene_respaldo' => false,
    ];

    protected $fillable = [
        'gestion', 'ciudad', 'numero_orden', 'beneficiario_id',
        'beneficiario_nombre', 'beneficiario_apellidos', 'beneficiario_ci_nit',
        'beneficiario_direccion', 'beneficiario_telefono',
        'a_la_orden_de', 'monto_total', 'retencion_7', 'retencion_35',
        'neto_pagar', 'concepto', 'categoria_gasto_id',
        'concepto_pago', 'numero_fojas', 'tiene_respaldo', 'creado_por',
        'liquidador_id', 'liquidador_texto', 'aprobado_por', 'fecha_orden', 'fecha_aprobacion',
        'fecha_cierre', 'estado', 'observaciones',
    ];

    protected $casts = [
        'monto_total'         => 'decimal:2',
        'retencion_7'         => 'decimal:2',
        'retencion_35'        => 'decimal:2',
        'neto_pagar'          => 'decimal:2',
        'fecha_orden'         => 'datetime',
        'fecha_aprobacion'    => 'datetime',
        'fecha_cierre'        => 'datetime',
        'tiene_respaldo'      => 'boolean',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $orden) {
            if (empty($orden->numero_orden)) {
                $orden->numero_orden = DB::transaction(function () {
                    $year = date('Y');
                    $ultimo = self::where('gestion', $year)
                        ->lockForUpdate()
                        ->orderBy('id', 'desc')
                        ->first();
                    $numero = ($ultimo ? $ultimo->id : 0) + 1;
                    return 'OP-' . $year . '-' . str_pad($numero, 5, '0', STR_PAD_LEFT);
                });
            }
        });
    }

    // Etiquetas legibles para el estado
    public function getEstadoLabelAttribute(): string
    {
        return [
            'pendiente_tesoreria'        => 'Pendiente Tesorería',
            'enviado_financiera'         => 'Enviado a Financiera',
            'rechazado_financiera'       => 'Rechazado por Financiera',
            'enviado_contabilidad'       => 'Enviado a Contabilidad',
            'rechazado_contabilidad'     => 'Rechazado por Contabilidad',
            'cheque_generado'            => 'Cheque Generado',
            'enviado_presupuesto'        => 'Enviado a Presupuesto',
            'rechazado_presupuesto'      => 'Rechazado por Presupuesto',
            'enviado_financiera_cheque'  => 'Cheque Enviado a Financiera',
            'rechazado_financiera_cheque'=> 'Cheque Rechazado por Financiera',
            'enviado_administracion'     => 'Enviado a Administración',
            'rechazado_administracion'   => 'Rechazado por Administración',
            'en_caja'                    => 'En Caja',
            'entregado'                  => 'Entregado',
            'cobrado'                    => 'Cobrado',
            'revalidando'                => 'Revalidando',
            'revalidado'                 => 'Revalidado',
            'cerrado'                    => 'Cerrado',
            'anulado'                    => 'Anulado',
            'enviado_archivos'           => 'Enviado a Archivos',
            'archivado'                  => 'Archivado',
        ][$this->estado] ?? ucfirst($this->estado);
    }

    public function getBadgeColorAttribute(): string
    {
        return [
            'pendiente_tesoreria'       => 'yellow',
            'enviado_financiera'        => 'blue',
            'rechazado_financiera'      => 'red',
            'enviado_contabilidad'      => 'indigo',
            'rechazado_contabilidad'    => 'red',
            'cheque_generado'           => 'purple',
            'enviado_presupuesto'       => 'cyan',
            'rechazado_presupuesto'     => 'red',
            'enviado_financiera_cheque' => 'blue',
            'rechazado_financiera_cheque'=> 'red',
            'enviado_administracion'    => 'sky',
            'rechazado_administracion'  => 'red',
            'en_caja'                   => 'fuchsia',
            'entregado'                 => 'green',
            'cobrado'                   => 'blue',
            'revalidando'               => 'orange',
            'revalidado'               => 'gray',
            'cerrado'                   => 'gray',
            'anulado'                   => 'red',
            'enviado_archivos'          => 'orange',
            'archivado'                 => 'gray',
        ][$this->estado] ?? 'gray';
    }

    // Accessors para datos del beneficiario (snapshot优先)
    public function getBeneficiarioNombreAttribute()
    {
        return $this->attributes['beneficiario_nombre'] ?? $this->beneficiario?->nombre_razon_social;
    }

    public function getBeneficiarioApellidosAttribute()
    {
        return $this->attributes['beneficiario_apellidos'] ?? $this->beneficiario?->apellidos;
    }

    public function getBeneficiarioCiNitAttribute()
    {
        return $this->attributes['beneficiario_ci_nit'] ?? $this->beneficiario?->ci_nit;
    }

    public function getBeneficiarioDireccionAttribute()
    {
        return $this->attributes['beneficiario_direccion'] ?? $this->beneficiario?->direccion;
    }

    public function getBeneficiarioTelefonoAttribute()
    {
        return $this->attributes['beneficiario_telefono'] ?? $this->beneficiario?->telefono;
    }

    // Relaciones
    public function beneficiario()
    {
        return $this->belongsTo(Beneficiario::class);
    }

    public function categoriaGasto()
    {
        return $this->belongsTo(CategoriaGasto::class);
    }

    public function creador()
    {
        return $this->belongsTo(User::class, 'creado_por');
    }

    public function liquidador()
    {
        return $this->belongsTo(User::class, 'liquidador_id');
    }

    public function aprobadoPor()
    {
        return $this->belongsTo(User::class, 'aprobado_por');
    }

    public function cheque()
    {
        return $this->hasOne(Cheque::class);
    }

    public function trackingHistorial()
    {
        return $this->hasMany(TrackingHistorial::class);
    }

    public function documentosAdjuntos()
    {
        return $this->hasMany(DocumentoAdjunto::class);
    }
}
