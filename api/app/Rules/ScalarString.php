<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class ScalarString implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_string($value) && ! is_numeric($value)) {
            $fail('El campo :attribute debe ser texto plano.');

            return;
        }

        if (is_array($value) || is_object($value)) {
            $fail('El campo :attribute no acepta estructuras complejas.');
        }
    }
}
