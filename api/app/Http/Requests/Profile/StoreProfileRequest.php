<?php

namespace App\Http\Requests\Profile;

use App\Http\Requests\BaseFormRequest;
use App\Rules\EnsuresAllowedSectionKeys;
use App\Rules\ScalarString;

class StoreProfileRequest extends BaseFormRequest
{
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:120', new ScalarString()],
            'section_keys' => ['required', 'array', 'min:1', new EnsuresAllowedSectionKeys()],
            'section_keys.*' => ['string'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'El nombre del perfil es obligatorio.',
            'name.max' => 'El nombre no puede superar 120 caracteres.',
            'section_keys.required' => 'Marca al menos una sección.',
            'section_keys.min' => 'Marca al menos una sección.',
        ];
    }
}
