<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

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
            'email'    => [
                'required', 'string', 'email', 'max:255',
                Rule::unique('users')->ignore($userId?->id)->whereNull('deleted_at'),
            ],
            'cargo'    => 'nullable|string|max:255',
            'telefono' => 'nullable|string|max:20',
            'area_id'  => 'nullable|exists:areas,id',
        ];

        if ($this->isMethod('POST')) {
            $rules['password'] = ['required', 'string', 'min:8', 'confirmed', 'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])/'];
            $rules['role']     = 'required|exists:roles,name';
        } else {
            $rules['password'] = ['nullable', 'string', 'min:8', 'confirmed', 'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])/'];
            $rules['role']     = 'nullable|exists:roles,name';
        }

        return $rules;
    }

    public function messages(): array
    {
        return [
            'password.regex' => 'La contraseña debe contener al menos una mayúscula, una minúscula, un número y un carácter especial (@$!%*?&).',
        ];
    }
}
