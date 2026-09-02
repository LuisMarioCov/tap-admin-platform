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
}
