<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class BeneficiarioRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // La autorización se maneja en el constructor del controller via middleware
    }

    public function rules(): array
    {
        return [
            'tipo'               => 'required|in:persona,empresa',
            'nombre_razon_social'=> 'required|string|max:200',
            'apellidos'          => 'nullable|string|max:100',
            'ci_nit'             => 'nullable|string|max:30',
            'direccion'          => 'nullable|string|max:255',
            'telefono'           => 'nullable|string|max:20',
            'email'              => 'nullable|email|max:100',
            'activo'             => 'sometimes|boolean',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'activo' => $this->has('activo') ? true : false,
        ]);
    }
}
