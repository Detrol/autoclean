<?php

namespace Database\Seeders;

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
                StationSeeder::class,        // Skapa stationer först
                TaskTemplateSeeder::class,   // Skapa task templates
                TaskSeeder::class,           // Skapa uppgifter (kräver stationer)
                AdminUserSeeder::class,      // Skapa användare och tilldela stationer
                InventoryItemSeeder::class,  // Skapa inventory items
            ]);
        }
    }
}
