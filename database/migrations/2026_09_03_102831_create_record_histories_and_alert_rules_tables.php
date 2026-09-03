<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('record_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('scraping_projects')->onDelete('cascade');
            $table->foreignId('record_id')->nullable()->constrained('extracted_records')->onDelete('cascade');
            $table->string('field_name');
            $table->text('old_value')->nullable();
            $table->text('new_value')->nullable();
            $table->string('change_type')->default('modified'); // price_drop, price_increase, stock_change, modified
            $table->decimal('percentage_delta', 8, 2)->nullable();
            $table->timestamps();

            $table->index(['project_id', 'created_at']);
        });

        Schema::create('alert_rules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('scraping_projects')->onDelete('cascade');
            $table->string('rule_name');
            $table->string('field_name');
            $table->string('operator'); // <, >, ==, contains, drops_by_percent
            $table->string('target_value');
            $table->string('channel')->default('webhook'); // webhook, email, log
            $table->string('destination')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamp('last_triggered_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('alert_rules');
        Schema::dropIfExists('record_histories');
    }
};