<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class ApiKey extends Model
{
    protected $fillable = [
        'name', 'key', 'rate_limit_per_minute',
        'is_active', 'last_used_at'
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            if (empty($model->key)) {
                $model->key = 'omni_' . Str::random(32);
            }
        });
    }
}