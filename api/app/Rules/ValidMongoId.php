<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use MongoDB\BSON\ObjectId;

class ValidMongoId implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_string($value) || ! preg_match('/^[a-f\d]{24}$/i', $value)) {
            $fail('El campo :attribute no es un identificador válido.');

            return;
        }

        try {
            new ObjectId($value);
        } catch (\Throwable) {
            $fail('El campo :attribute no es un identificador válido.');
        }
    }
}
