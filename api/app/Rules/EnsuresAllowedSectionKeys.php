<?php

namespace App\Rules;

use App\Models\Section;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class EnsuresAllowedSectionKeys implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_array($value)) {
            $fail('Las secciones deben enviarse como arreglo.');

            return;
        }

        $allowed = Section::allowedKeys();

        foreach ($value as $key) {
            if (! is_string($key) || ! in_array($key, $allowed, true)) {
                $fail('Una o más secciones no están permitidas.');

                return;
            }
        }
    }
}
