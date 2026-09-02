<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use MongoDB\Laravel\Eloquent\Model;

class Profile extends Model
{
    use SoftDeletes;

    protected $connection = 'mongodb';

    protected $collection = 'profiles';

    protected $fillable = [
        'code',
        'name',
        'section_keys',
    ];

    protected function casts(): array
    {
        return [
            'section_keys' => 'array',
            'deleted_at' => 'datetime',
        ];
    }
}
