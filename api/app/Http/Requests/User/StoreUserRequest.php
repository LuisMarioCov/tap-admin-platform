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

    public function messages(): array
    {
        return [
            'name.required' => 'El nombre es obligatorio.',
            'name.max' => 'El nombre no puede superar 120 caracteres.',
            'email.required' => 'El correo es obligatorio.',
            'email.email' => 'Escribe un correo válido (ejemplo: usuario@correo.com).',
            'email.unique' => 'Ese correo ya está registrado.',
            'password.required' => 'La contraseña es obligatoria.',
            'password.min' => 'La contraseña debe tener al menos 8 caracteres, con letras y números.',
            'password.letters' => 'La contraseña debe incluir al menos una letra.',
            'password.numbers' => 'La contraseña debe incluir al menos un número.',
            'phone.max' => 'El teléfono no puede superar 20 caracteres.',
            'country_code.max' => 'El código de país no puede superar 6 caracteres (ejemplo: +52).',
            'profile_ids.required' => 'Selecciona al menos un perfil.',
            'profile_ids.min' => 'Selecciona al menos un perfil.',
            'photo.required' => 'La foto es obligatoria al crear un usuario.',
            'photo.file' => 'Debes adjuntar un archivo de foto.',
            'photo.mimes' => 'La foto debe ser JPG, PNG o WebP.',
            'photo.max' => 'La foto excede 2 MB, archivo demasiado grande.',
        ];
    }
}
