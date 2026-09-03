<?php

namespace App\Http\Requests\Product;

use App\Http\Requests\BaseFormRequest;
use App\Rules\ScalarString;

class StoreProductRequest extends BaseFormRequest
{
    /** Price cap is integer max:999, not ScalarString. */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:120', new ScalarString()],
            'brand' => ['required', 'string', 'max:120', new ScalarString()],
            'price' => ['required', 'integer', 'min:1', 'max:999'],
        ];
    }
}
