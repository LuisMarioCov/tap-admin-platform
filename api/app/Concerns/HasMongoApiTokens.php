<?php

namespace App\Concerns;

use DateTimeInterface;
use Laravel\Sanctum\HasApiTokens;

trait HasMongoApiTokens
{
    use HasApiTokens;

    public function createToken(string $name, array $abilities = ['*'], ?DateTimeInterface $expiresAt = null): object
    {
        $plainTextToken = $this->generateTokenString();

        $token = $this->tokens()->create([
            'name' => $name,
            'token' => hash('sha256', $plainTextToken),
            'abilities' => $abilities,
            'expires_at' => $expiresAt,
        ]);

        return (object) [
            'plainTextToken' => $token->getKey().'|'.$plainTextToken,
            'accessToken' => $token,
        ];
    }
}
