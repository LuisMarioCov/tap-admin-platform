<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use MongoDB\Laravel\Eloquent\Model;

class Product extends Model
{
    use SoftDeletes;

    protected $connection = 'mongodb';

    protected $collection = 'products';

    protected $fillable = [
        'code',
        'name',
        'brand',
        'price',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'integer',
            'deleted_at' => 'datetime',
        ];
    }
}
