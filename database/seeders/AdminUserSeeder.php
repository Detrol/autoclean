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
        // Skapa admin-användare
        $adminUser = \App\Models\User::firstOrCreate(
            ['email' => 'admin@autoclean.se'],
            [
                'name' => 'Admin Användare',
                'password' => \Illuminate\Support\Facades\Hash::make('password'),
                'is_admin' => true,
                'email_verified_at' => now(),
            ]
        );

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

        // Tilldela användare till stationer efter att StationSeeder kört
        $hammaroStation = \App\Models\Station::where('name', 'Hammarö')->first();
        $vaxnasStation = \App\Models\Station::where('name', 'Våxnäs')->first();

        if ($hammaroStation && $vaxnasStation) {
            // Admin får tillgång till alla stationer
            $adminUser->stations()->syncWithoutDetaching([$hammaroStation->id, $vaxnasStation->id]);
            
            // Anställd får tillgång till båda stationerna
            $employee->stations()->syncWithoutDetaching([$hammaroStation->id, $vaxnasStation->id]);
        }
    }
}
