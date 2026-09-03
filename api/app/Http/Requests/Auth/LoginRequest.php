<?php

namespace App\Http\Requests\Auth;

use App\Http\Requests\BaseFormRequest;
use App\Rules\ScalarString;

class LoginRequest extends BaseFormRequest
{
    public function rules(): array
    {
        return [
            'email' => ['required', 'email', new ScalarString()],
            'password' => ['required', 'string', 'min:1', new ScalarString()],
        ];
    }

    public function messages(): array
    {
        return [
            'email.required' => 'El correo es obligatorio.',
            'email.email' => 'Escribe un correo válido (ejemplo: usuario@correo.com).',
            'password.required' => 'La contraseña es obligatoria.',
        ];
    }
}
