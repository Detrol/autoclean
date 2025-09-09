<?php

namespace Database\Seeders;

use App\Models\Station;
use Illuminate\Database\Seeder;

class StationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $stations = [
            [
                'name' => 'Hammarö',
                'description' => 'Tvätthall Hammarö - Huvudstation med automathall och portal',
                'is_active' => true,
            ],
            [
                'name' => 'Våxnäs',
                'description' => 'Tvätthall Våxnäs - Tvätthall med basservice',
                'is_active' => true,
            ],
        ];

        foreach ($stations as $stationData) {
            Station::firstOrCreate(
                ['name' => $stationData['name']],
                $stationData
            );
        }
    }
}