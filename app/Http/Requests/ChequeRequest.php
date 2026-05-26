<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ChequeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'orden_pago_id'  => 'required|exists:ordenes_pago,id',
            'fecha_emision'  => 'required|date',
            'fecha_pago'     => 'nullable|date|after_or_equal:fecha_emision',
            'observaciones'  => 'nullable|string|max:500',
        ];
    }
}
