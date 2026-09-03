<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AlertRule extends Model
{
    protected $fillable = [
        'project_id', 'rule_name', 'field_name', 'operator',
        'target_value', 'channel', 'destination', 'is_active', 'last_triggered_at'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'last_triggered_at' => 'datetime'
    ];

    public function project()
    {
        return $this->belongsTo(ScrapingProject::class, 'project_id');
    }
}