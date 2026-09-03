<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('scraping_projects', function (Blueprint $table) {
            $table->string('auth_type')->default('none')->after('container_selector'); // none, form_login, cookies, bearer
            $table->json('auth_config')->nullable()->after('auth_type');
            $table->longText('session_cookies')->nullable()->after('auth_config');
        });
    }

    public function down(): void
    {
        Schema::table('scraping_projects', function (Blueprint $table) {
            $table->dropColumn(['auth_type', 'auth_config', 'session_cookies']);
        });
    }
};