<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $settings = [
            [
                'key' => 'task_rollover_enabled',
                'value' => '1',
                'type' => 'boolean',
                'group' => 'tasks',
                'label' => 'Aktivera Task Rollover',
                'description' => 'Flytta automatiskt försenade uppgifter från tidigare dagar till idag. Gäller endast icke-dagliga uppgifter.',
            ],
            [
                'key' => 'admin_requires_clock_in',
                'value' => '0',
                'type' => 'boolean',
                'group' => 'tasks',
                'label' => 'Kräv Inklockning för Admins',
                'description' => 'Kräv att administratörer är inklockade på en station för att slutföra uppgifter. Om inaktiverat kan admins alltid slutföra uppgifter.',
            ],
            [
                'key' => 'auto_clock_out_enabled',
                'value' => '1',
                'type' => 'boolean',
                'group' => 'timekeeping',
                'label' => 'Aktivera automatisk utklocking',
                'description' => 'Klocka automatiskt ut användare som glömt att klocka ut.',
            ],
            [
                'key' => 'auto_clock_out_hours',
                'value' => '12',
                'type' => 'integer',
                'group' => 'timekeeping',
                'label' => 'Max timmar innan automatisk utklocking',
                'description' => 'Antal timmar efter inklockning innan systemet automatiskt klockar ut.',
            ],
        ];

        foreach ($settings as $setting) {
            Setting::updateOrCreate(
                ['key' => $setting['key']],
                $setting
            );
        }
    }
}
