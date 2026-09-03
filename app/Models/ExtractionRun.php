<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExtractionRun extends Model
{
    protected $fillable = [
        'project_id', 'status', 'records_extracted',
        'records_new', 'records_updated', 'execution_time_ms',
        'extraction_accuracy', 'error_log', 'started_at', 'completed_at'
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'extraction_accuracy' => 'float',
    ];

    public function project()
    {
        return $this->belongsTo(ScrapingProject::class, 'project_id');
    }

    public function healingLogs()
    {
        return $this->hasMany(SelfHealingLog::class, 'run_id');
    }
}