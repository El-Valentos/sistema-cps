<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

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
            'codigo'      => [
                'required', 'string', 'max:50',
                Rule::unique('areas')->ignore($areaId?->id)->whereNull('deleted_at'),
            ],
            'descripcion' => 'nullable|string',
            'orden_flujo' => 'required|integer',
        ];
    }
}
