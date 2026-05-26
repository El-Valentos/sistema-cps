<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class OrdenPagoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $isEdit = $this->isMethod('PUT');
        
        return [
            'beneficiario_id'       => $isEdit ? 'nullable|exists:beneficiarios,id' : 'required|exists:beneficiarios,id',
            'nombre_razon_social'  => 'nullable|string|max:255',
            'apellidos'            => 'nullable|string|max:255',
            'ci_nit'              => 'nullable|string|max:50',
            'telefono'            => 'nullable|string|max:30',
            'direccion'           => 'nullable|string|max:255',
            'a_la_orden_de'       => 'nullable|string|max:200',
            'monto_total'         => 'required|numeric|min:0.01',
            'aplica_retencion_7'   => 'nullable|boolean',
            'aplica_retencion_35'  => 'nullable|boolean',
            'concepto'            => 'required|string|max:1000',
            'categoria_gasto_id'  => 'nullable|exists:categorias_gasto,id',
            'concepto_pago'     => 'nullable|string|max:50',
            'numero_fojas'        => 'nullable|integer|min:0',
            'liquidador_id'       => 'nullable|exists:users,id',
            'fecha_orden'         => 'required|date',
            'observaciones'        => 'nullable|string|max:1000',
            'documentos'          => 'nullable|array|max:15',
            'documentos.*'        => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240',
        ];
    }
}
