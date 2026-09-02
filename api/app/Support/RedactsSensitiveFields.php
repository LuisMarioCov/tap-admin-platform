<?php

namespace App\Support;

class RedactsSensitiveFields
{
    /** @var list<string> */
    private const SENSITIVE = [
        'password',
        'remember_token',
        'token',
        'reset_token',
    ];

    /**
     * @param  array<string, mixed>|null  $payload
     * @return array<string, mixed>|null
     */
    public static function redact(?array $payload): ?array
    {
        if ($payload === null) {
            return null;
        }

        $redacted = $payload;

        foreach (self::SENSITIVE as $field) {
            if (array_key_exists($field, $redacted)) {
                $redacted[$field] = '[REDACTED]';
            }
        }

        return $redacted;
    }
}
