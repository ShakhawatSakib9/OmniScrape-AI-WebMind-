<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Webhook extends Model
{
    protected $fillable = [
        'project_id', 'target_url', 'secret',
        'event_on_new_records', 'event_on_updated_records',
        'event_on_self_healing', 'is_active'
    ];

    protected $casts = [
        'event_on_new_records' => 'boolean',
        'event_on_updated_records' => 'boolean',
        'event_on_self_healing' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function project()
    {
        return $this->belongsTo(ScrapingProject::class, 'project_id');
    }
}