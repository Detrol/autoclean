<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Only run in non-production or when specifically needed
        if (app()->environment('local', 'staging') || request()->has('force-seed')) {
            $this->call([
                AdminUserSeeder::class,
            ]);
        }
    }
}
