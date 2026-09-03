<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SelfHealingLog extends Model
{
    protected $fillable = [
        'project_id', 'run_id', 'field_name',
        'broken_selector', 'repaired_selector',
        'old_confidence', 'new_confidence',
        'sample_extracted_value', 'reasoning_log'
    ];

    protected $casts = [
        'old_confidence' => 'float',
        'new_confidence' => 'float',
    ];

    public function project()
    {
        return $this->belongsTo(ScrapingProject::class, 'project_id');
    }

    public function run()
    {
        return $this->belongsTo(ExtractionRun::class, 'run_id');
    }
}