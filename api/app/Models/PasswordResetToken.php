<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

class PasswordResetToken extends Model
{
    public $timestamps = false;

    protected $connection = 'mongodb';

    protected $collection = 'password_reset_tokens';

    protected $fillable = [
        'email',
        'token_hash',
        'expires_at',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'expires_at' => 'datetime',
            'created_at' => 'datetime',
        ];
    }
}
