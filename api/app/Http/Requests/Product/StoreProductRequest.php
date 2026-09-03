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

    public function messages(): array
    {
        return [
            'name.required' => 'El nombre es obligatorio.',
            'name.max' => 'El nombre no puede superar 120 caracteres.',
            'brand.required' => 'La marca es obligatoria.',
            'brand.max' => 'La marca no puede superar 120 caracteres.',
            'price.required' => 'El precio es obligatorio.',
            'price.integer' => 'El precio debe ser un número entero, sin decimales.',
            'price.min' => 'El precio mínimo es 1.',
            'price.max' => 'El precio no puede ser mayor a 999.',
        ];
    }
}
