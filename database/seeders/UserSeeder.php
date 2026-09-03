<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\ScrapingProject;
use App\Models\ApiKey;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::updateOrCreate(
            ['email' => 'sakib@omniscrape.io'],
            [
                'name' => 'Shakhawat Sakib',
                'password' => Hash::make('password'),
            ]
        );

        // Assign existing projects to this user
        ScrapingProject::whereNull('user_id')->update(['user_id' => $user->id]);

        // Generate sample API Key
        ApiKey::updateOrCreate(
            ['name' => 'Production Master Key'],
            [
                'key' => 'omni_live_' . Str::random(32),
                'rate_limit_per_minute' => 120,
                'is_active' => true,
            ]
        );
    }
}