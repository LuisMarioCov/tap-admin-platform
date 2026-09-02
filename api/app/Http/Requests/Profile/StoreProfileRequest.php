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
}
