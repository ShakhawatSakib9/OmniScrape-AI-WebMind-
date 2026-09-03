<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Proxy extends Model
{
    protected $fillable = [
        'ip_address', 'port', 'protocol', 'username',
        'password', 'country_code', 'status', 'latency_ms',
        'total_requests', 'failed_requests', 'last_used_at'
    ];

    protected $casts = [
        'last_used_at' => 'datetime'
    ];

    public function getFormattedServerAttribute(): string
    {
        return "{$this->protocol}://{$this->ip_address}:{$this->port}";
    }
}