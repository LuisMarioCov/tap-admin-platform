<?php

namespace App\Http\Requests\User;

use App\Rules\ScalarString;
use App\Rules\ValidMongoId;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class UpdateUserRequest extends StoreUserRequest
{
    public function rules(): array
    {
        $userId = (string) $this->route('user');

        return [
            'name' => ['required', 'string', 'max:120', new ScalarString()],
            'email' => ['required', 'email', Rule::unique('users', 'email')->ignore($userId, '_id'), new ScalarString()],
            'password' => ['nullable', Password::min(8)->letters()->numbers()],
            'phone' => ['nullable', 'string', 'max:20', new ScalarString()],
            'country_code' => ['nullable', 'string', 'max:6', new ScalarString()],
            'profile_ids' => ['required', 'array', 'min:1'],
            'profile_ids.*' => ['required', 'string', new ValidMongoId()],
            'photo' => ['sometimes', 'file', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ];
    }
}
