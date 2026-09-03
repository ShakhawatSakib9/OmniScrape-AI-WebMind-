<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('proxies', function (Blueprint $table) {
            $table->id();
            $table->string('ip_address');
            $table->integer('port');
            $table->string('protocol')->default('http'); // http, https, socks5
            $table->string('username')->nullable();
            $table->string('password')->nullable();
            $table->string('country_code', 3)->nullable();
            $table->string('status')->default('active'); // active, banned, slow
            $table->integer('latency_ms')->default(150);
            $table->integer('total_requests')->default(0);
            $table->integer('failed_requests')->default(0);
            $table->timestamp('last_used_at')->nullable();
            $table->timestamps();

            $table->index(['status', 'latency_ms']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('proxies');
    }
};