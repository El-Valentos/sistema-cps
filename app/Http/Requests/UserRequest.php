<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $userId = $this->route('user');

        $rules = [
            'name'     => 'required|string|max:255',
            'email'    => 'required|string|email|max:255|unique:users,email,' . ($userId?->id ?? 'NULL'),
            'cargo'    => 'nullable|string|max:255',
            'telefono' => 'nullable|string|max:20',
            'area_id'  => 'nullable|exists:areas,id',
        ];

        if ($this->isMethod('POST')) {
            $rules['password'] = 'required|string|min:8|confirmed';
            $rules['role']     = 'required|exists:roles,name';
        } else {
            $rules['password'] = 'nullable|string|min:8|confirmed';
            $rules['role']     = 'nullable|exists:roles,name';
        }

        return $rules;
    }
}
