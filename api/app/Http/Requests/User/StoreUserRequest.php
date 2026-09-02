<?php

namespace App\Http\Requests\User;

use App\Http\Requests\BaseFormRequest;
use App\Rules\ScalarString;
use App\Rules\ValidMongoId;
use Illuminate\Validation\Rules\Password;

class StoreUserRequest extends BaseFormRequest
{
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:120', new ScalarString()],
            'email' => ['required', 'email', 'unique:users,email', new ScalarString()],
            'password' => ['required', Password::min(8)->letters()->numbers()],
            'phone' => ['nullable', 'string', 'max:20', new ScalarString()],
            'country_code' => ['nullable', 'string', 'max:6', new ScalarString()],
            'profile_ids' => ['required', 'array', 'min:1'],
            'profile_ids.*' => ['required', 'string', new ValidMongoId()],
            'photo' => ['required', 'file', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ];
    }
}
