<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProjectSchema extends Model
{
    protected $fillable = [
        'project_id', 'field_name', 'field_label',
        'field_type', 'is_required', 'description'
    ];

    public function project()
    {
        return $this->belongsTo(ScrapingProject::class, 'project_id');
    }

    public function selector()
    {
        return $this->hasOne(ProjectSelector::class, 'schema_id');
    }
}