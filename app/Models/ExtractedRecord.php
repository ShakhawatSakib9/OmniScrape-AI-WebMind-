<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExtractedRecord extends Model
{
    protected $fillable = [
        'project_id', 'record_hash', 'data_json',
        'first_seen_at', 'last_seen_at', 'status'
    ];

    protected $casts = [
        'data_json' => 'array',
        'first_seen_at' => 'datetime',
        'last_seen_at' => 'datetime',
    ];

    public function project()
    {
        return $this->belongsTo(ScrapingProject::class, 'project_id');
    }
}