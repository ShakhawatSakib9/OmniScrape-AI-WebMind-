<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProjectSelector extends Model
{
    protected $fillable = [
        'project_id', 'schema_id', 'field_name',
        'primary_selector', 'fallback_selectors',
        'attribute_target', 'confidence_score',
        'status', 'last_successful_extraction_at'
    ];

    protected $casts = [
        'fallback_selectors' => 'array',
        'confidence_score' => 'float',
    ];

    public function project()
    {
        return $this->belongsTo(ScrapingProject::class, 'project_id');
    }

    public function schema()
    {
        return $this->belongsTo(ProjectSchema::class, 'schema_id');
    }
}