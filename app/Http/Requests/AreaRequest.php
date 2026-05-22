<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AreaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $areaId = $this->route('area');

        return [
            'nombre'      => 'required|string|max:255',
            'codigo'      => 'required|string|max:50|unique:areas,codigo,' . ($areaId?->id ?? 'NULL'),
            'descripcion' => 'nullable|string',
            'orden_flujo' => 'required|integer',
        ];
    }
}
