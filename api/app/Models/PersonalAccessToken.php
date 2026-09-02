<?php

namespace App\Models;

use Laravel\Sanctum\Contracts\HasAbilities;
use MongoDB\Laravel\Eloquent\Model;

class PersonalAccessToken extends Model implements HasAbilities
{
    protected $connection = 'mongodb';

    protected $collection = 'personal_access_tokens';

    protected $casts = [
        'abilities' => 'json',
        'last_used_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    protected $fillable = [
        'name',
        'token',
        'abilities',
        'expires_at',
        'tokenable_id',
        'tokenable_type',
    ];

    protected $hidden = [
        'token',
    ];

    public function tokenable()
    {
        return $this->morphTo('tokenable');
    }

    public static function findToken($token)
    {
        if (strpos($token, '|') === false) {
            return static::query()->where('token', hash('sha256', $token))->first();
        }

        [$id, $plainToken] = explode('|', $token, 2);
        $instance = static::query()->find($id);

        if ($instance !== null && hash_equals($instance->token, hash('sha256', $plainToken))) {
            return $instance;
        }

        return null;
    }

    public function can($ability): bool
    {
        $abilities = $this->abilities ?? [];

        return in_array('*', $abilities, true) || in_array($ability, $abilities, true);
    }

    public function cant($ability): bool
    {
        return ! $this->can($ability);
    }
}
