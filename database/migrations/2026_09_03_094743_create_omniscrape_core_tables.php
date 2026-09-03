<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('scraping_projects', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('target_url');
            $table->text('prompt');
            $table->string('frequency_cron')->nullable()->default('0 8 * * *');
            $table->enum('status', ['draft', 'active', 'paused', 'healing', 'failed'])->default('draft');
            $table->enum('pagination_type', ['none', 'next_button', 'infinite_scroll', 'page_param'])->default('none');
            $table->string('pagination_selector')->nullable();
            $table->integer('max_pages')->default(1);
            $table->integer('items_per_page')->nullable();
            $table->string('container_selector')->nullable();
            $table->timestamp('last_run_at')->nullable();
            $table->timestamp('next_run_at')->nullable();
            $table->timestamps();
        });

        Schema::create('project_schemas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('scraping_projects')->onDelete('cascade');
            $table->string('field_name');
            $table->string('field_label')->nullable();
            $table->enum('field_type', ['string', 'number', 'price', 'image_url', 'link', 'boolean', 'html'])->default('string');
            $table->boolean('is_required')->default(false);
            $table->text('description')->nullable();
            $table->timestamps();
            $table->unique(['project_id', 'field_name']);
        });

        Schema::create('project_selectors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('scraping_projects')->onDelete('cascade');
            $table->foreignId('schema_id')->constrained('project_schemas')->onDelete('cascade');
            $table->string('field_name');
            $table->string('primary_selector');
            $table->json('fallback_selectors')->nullable();
            $table->enum('attribute_target', ['text', 'href', 'src', 'inner_html', 'value'])->default('text');
            $table->decimal('confidence_score', 4, 2)->default(1.00);
            $table->enum('status', ['active', 'degraded', 'repaired'])->default('active');
            $table->timestamp('last_successful_extraction_at')->nullable();
            $table->timestamps();
        });

        Schema::create('extracted_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('scraping_projects')->onDelete('cascade');
            $table->string('record_hash', 64)->index();
            $table->json('data_json');
            $table->timestamp('first_seen_at')->useCurrent();
            $table->timestamp('last_seen_at')->useCurrent();
            $table->enum('status', ['active', 'updated', 'archived'])->default('active');
            $table->timestamps();
            $table->unique(['project_id', 'record_hash']);
        });

        Schema::create('extraction_runs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('scraping_projects')->onDelete('cascade');
            $table->enum('status', ['running', 'success', 'healed', 'failed'])->default('running');
            $table->integer('records_extracted')->default(0);
            $table->integer('records_new')->default(0);
            $table->integer('records_updated')->default(0);
            $table->integer('execution_time_ms')->default(0);
            $table->decimal('extraction_accuracy', 5, 2)->default(100.00);
            $table->text('error_log')->nullable();
            $table->timestamp('started_at')->useCurrent();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
        });

        Schema::create('self_healing_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('scraping_projects')->onDelete('cascade');
            $table->foreignId('run_id')->nullable()->constrained('extraction_runs')->onDelete('set null');
            $table->string('field_name');
            $table->string('broken_selector');
            $table->string('repaired_selector');
            $table->decimal('old_confidence', 4, 2)->default(0.00);
            $table->decimal('new_confidence', 4, 2)->default(0.00);
            $table->text('sample_extracted_value')->nullable();
            $table->text('reasoning_log')->nullable();
            $table->timestamps();
        });

        Schema::create('api_keys', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('key', 64)->unique();
            $table->integer('rate_limit_per_minute')->default(60);
            $table->boolean('is_active')->default(true);
            $table->timestamp('last_used_at')->nullable();
            $table->timestamps();
        });

        Schema::create('webhooks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('scraping_projects')->onDelete('cascade');
            $table->string('target_url');
            $table->string('secret')->nullable();
            $table->boolean('event_on_new_records')->default(true);
            $table->boolean('event_on_updated_records')->default(true);
            $table->boolean('event_on_self_healing')->default(true);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('webhooks');
        Schema::dropIfExists('api_keys');
        Schema::dropIfExists('self_healing_logs');
        Schema::dropIfExists('extraction_runs');
        Schema::dropIfExists('extracted_records');
        Schema::dropIfExists('project_selectors');
        Schema::dropIfExists('project_schemas');
        Schema::dropIfExists('scraping_projects');
    }
};