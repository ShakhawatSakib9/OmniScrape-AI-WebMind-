<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RecordHistory extends Model
{
    protected $fillable = [
        'project_id', 'record_id', 'field_name', 'old_value',
        'new_value', 'change_type', 'percentage_delta'
    ];

    public function project()
    {
        return $this->belongsTo(ScrapingProject::class, 'project_id');
    }

    public function record()
    {
        return $this->belongsTo(ExtractedRecord::class, 'record_id');
    }
}