<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        \App\Models\User::firstOrCreate(
            ['email' => 'admin@autoclean.se'],
            [
                'name' => 'Admin Användare',
                'password' => \Illuminate\Support\Facades\Hash::make('password'),
                'is_admin' => true,
                'email_verified_at' => now(),
            ]
        );

        // Skapa test-stationer
        $stations = [
            ['name' => 'Station 1', 'description' => 'Huvudstation för biltvätt', 'is_active' => true],
            ['name' => 'Station 2', 'description' => 'Sekundärstation för tvätt', 'is_active' => true],
            ['name' => 'Dammsugare', 'description' => 'Station för dammsugning', 'is_active' => true],
        ];

        $createdStations = [];
        foreach ($stations as $stationData) {
            $station = \App\Models\Station::firstOrCreate(
                ['name' => $stationData['name']],
                $stationData
            );
            $createdStations[] = $station;
        }

        // Tilldela admin-användaren till alla stationer
        $adminUser = \App\Models\User::where('email', 'admin@autoclean.se')->first();
        if ($adminUser) {
            $adminUser->stations()->syncWithoutDetaching($createdStations);
        }

        // Skapa en vanlig anställd
        $employee = \App\Models\User::firstOrCreate(
            ['email' => 'employee@autoclean.se'],
            [
                'name' => 'Anställd Användare',
                'password' => \Illuminate\Support\Facades\Hash::make('password'),
                'is_admin' => false,
                'email_verified_at' => now(),
            ]
        );

        // Tilldela anställd till Station 1 och Station 2
        $employee->stations()->syncWithoutDetaching([$createdStations[0]->id, $createdStations[1]->id]);

        // Skapa test-uppgifter
        $tasks = [
            [
                'station_id' => $createdStations[0]->id,
                'name' => 'Rengöring av tvättutrustning',
                'description' => 'Rengör alla tvättborstar och slangar',
                'interval_type' => 'daily',
                'interval_value' => 1,
                'default_due_time' => '18:00',
                'is_active' => true,
            ],
            [
                'station_id' => $createdStations[0]->id,
                'name' => 'Kontrollera kemikalienivåer',
                'description' => 'Se till att alla kemikalier är påfyllda',
                'interval_type' => 'daily',
                'interval_value' => 1,
                'default_due_time' => '09:00',
                'is_active' => true,
            ],
            [
                'station_id' => $createdStations[1]->id,
                'name' => 'Rengöring av station',
                'description' => 'Allmän rengöring av stationsområde',
                'interval_type' => 'daily',
                'interval_value' => 1,
                'default_due_time' => '17:00',
                'is_active' => true,
            ],
            [
                'station_id' => $createdStations[2]->id,
                'name' => 'Tömma dammsugare',
                'description' => 'Tömma och rengör dammsugarbehållare',
                'interval_type' => 'weekly',
                'interval_value' => 1,
                'default_due_time' => '16:00',
                'is_active' => true,
            ],
        ];

        foreach ($tasks as $taskData) {
            \App\Models\Task::firstOrCreate(
                [
                    'station_id' => $taskData['station_id'],
                    'name' => $taskData['name']
                ],
                $taskData
            );
        }
    }
}
