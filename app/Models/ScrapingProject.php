<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class ScrapingProject extends Model
{
    protected $fillable = [
        'uuid', 'name', 'slug', 'target_url', 'prompt',
        'frequency_cron', 'status', 'pagination_type',
        'pagination_selector', 'max_pages', 'items_per_page',
        'container_selector', 'auth_type', 'auth_config',
        'session_cookies', 'last_run_at', 'next_run_at'
    ];

    protected $casts = [
        'auth_config' => 'array',
        'last_run_at' => 'datetime',
        'next_run_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            if (empty($model->uuid)) {
                $model->uuid = (string) Str::uuid();
            }
            if (empty($model->slug)) {
                $model->slug = Str::slug($model->name) . '-' . Str::lower(Str::random(6));
            }
        });
    }

    public function schemas()
    {
        return $this->hasMany(ProjectSchema::class, 'project_id');
    }

    public function selectors()
    {
        return $this->hasMany(ProjectSelector::class, 'project_id');
    }

    public function records()
    {
        return $this->hasMany(ExtractedRecord::class, 'project_id');
    }

    public function runs()
    {
        return $this->hasMany(ExtractionRun::class, 'project_id')->orderBy('id', 'desc');
    }

    public function healingLogs()
    {
        return $this->hasMany(SelfHealingLog::class, 'project_id')->orderBy('id', 'desc');
    }

    public function webhooks()
    {
        return $this->hasMany(Webhook::class, 'project_id');
    }
}