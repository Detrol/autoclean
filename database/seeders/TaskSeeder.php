<?php

namespace Database\Seeders;

use App\Models\Station;
use App\Models\Task;
use Illuminate\Database\Seeder;

class TaskSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $hammaroStation = Station::where('name', 'Hammarö')->first();
        $vaxnasStation = Station::where('name', 'Våxnäs')->first();

        if (!$hammaroStation || !$vaxnasStation) {
            throw new \Exception('Stationer måste finnas innan uppgifter kan skapas. Kör StationSeeder först.');
        }

        // ==========================================
        // DAGLIG BASRUTIN (GUL) - 1 timme
        // Utförs VARJE besök på båda stationerna
        // ==========================================
        
        $dailyBaseTasks = [
            [
                'name' => 'Kontrollera och töm soptunnor',
                'description' => 'Kontrollera och töm soptunnor vid behov',
                'interval_type' => 'daily',
                'interval_value' => 1,
                'default_due_time' => '23:59',
            ],
            [
                'name' => 'Kontrollera kemikalienivåer',
                'description' => 'Kontrollera kemikalienivåer och fyll på vid behov',
                'interval_type' => 'daily',
                'interval_value' => 1,
                'default_due_time' => '23:59',
            ],
            [
                'name' => 'Rengör golv och väggar i tvättbås',
                'description' => 'Rengör golv och väggar i tvättbås',
                'interval_type' => 'daily',
                'interval_value' => 1,
                'default_due_time' => '23:59',
            ],
            [
                'name' => 'Allmän kontroll av tvätthallen',
                'description' => 'Allmän kontroll av tvätthallen',
                'interval_type' => 'daily',
                'interval_value' => 1,
                'default_due_time' => '23:59',
            ],
            [
                'name' => 'Funktionstest av dammsugare',
                'description' => 'Funktionstest av dammsugare',
                'interval_type' => 'daily',
                'interval_value' => 1,
                'default_due_time' => '23:59',
            ],
            [
                'name' => 'Kontroll av polletter och kvittosystem',
                'description' => 'Kontroll/rotering av polletter, dator och kvittosystem',
                'interval_type' => 'daily',
                'interval_value' => 1,
                'default_due_time' => '23:59',
            ],
            [
                'name' => 'Kontroll av slangar och munstycken',
                'description' => 'Kontroll av slangar och munstycken',
                'interval_type' => 'daily',
                'interval_value' => 1,
                'default_due_time' => '23:59',
            ],
        ];

        // Skapa dagliga uppgifter för båda stationerna
        foreach ([$hammaroStation, $vaxnasStation] as $station) {
            foreach ($dailyBaseTasks as $taskData) {
                $taskData['station_id'] = $station->id;
                Task::firstOrCreate(
                    [
                        'station_id' => $taskData['station_id'],
                        'name' => $taskData['name']
                    ],
                    $taskData
                );
            }
        }

        // ==========================================
        // HAMMARÖ SPECIFIKA UPPGIFTER
        // ==========================================

        $hammaroTasks = [
            // GRÖN - Automathall & Portal (måndag och vissa fredagar)
            [
                'name' => 'Grundlig rengöring av automathall',
                'description' => 'Grundlig rengöring av automathall (1,5 timmar)',
                'interval_type' => 'weekly',
                'interval_value' => 1,
                'recurrence_pattern' => [
                    'daysOfWeek' => ['monday']
                ],
                'default_due_time' => '23:59',
                'is_active' => true,
            ],
            [
                'name' => 'Rengöring av port och portal',
                'description' => 'Rengöring av port och portal vid automattvättens in/utgång',
                'interval_type' => 'weekly',
                'interval_value' => 1,
                'recurrence_pattern' => [
                    'daysOfWeek' => ['monday']
                ],
                'default_due_time' => '23:59',
                'is_active' => true,
            ],
            [
                'name' => 'Extra noggrann städning av maskindelar',
                'description' => 'Extra noggrann städning av maskindelar',
                'interval_type' => 'weekly',
                'interval_value' => 1,
                'recurrence_pattern' => [
                    'daysOfWeek' => ['monday']
                ],
                'default_due_time' => '23:59',
                'is_active' => true,
            ],

            // ORANGE - Tekniskt underhåll (onsdag och fredag)
            [
                'name' => 'Rengöring av reningsverk',
                'description' => 'Rengöring av reningsverk (1 timme)',
                'interval_type' => 'weekly',
                'interval_value' => 1,
                'recurrence_pattern' => [
                    'daysOfWeek' => ['wednesday']
                ],
                'default_due_time' => '23:59',
                'is_active' => true,
            ],
            [
                'name' => 'Lätt rengöring av maskinrum och toalett',
                'description' => 'Lätt rengöring av maskinrum och toalett',
                'interval_type' => 'weekly',
                'interval_value' => 1,
                'recurrence_pattern' => [
                    'daysOfWeek' => ['wednesday']
                ],
                'default_due_time' => '23:59',
                'is_active' => true,
            ],
            [
                'name' => 'Rengöring av pollett- och båsväxlare',
                'description' => 'Rengöring av pollett- och båsväxlare',
                'interval_type' => 'weekly',
                'interval_value' => 1,
                'recurrence_pattern' => [
                    'daysOfWeek' => ['wednesday']
                ],
                'default_due_time' => '23:59',
                'is_active' => true,
            ],
            [
                'name' => 'Kontroll av teknisk utrustning',
                'description' => 'Kontroll av teknisk utrustning',
                'interval_type' => 'weekly',
                'interval_value' => 1,
                'recurrence_pattern' => [
                    'daysOfWeek' => ['wednesday']
                ],
                'default_due_time' => '23:59',
                'is_active' => true,
            ],

            // BLÅ - Månadsunderhåll (ojämn vecka onsdag)
            [
                'name' => 'Containertömning vid behov',
                'description' => 'Containertömning vid behov',
                'interval_type' => 'weekly',
                'interval_value' => 2,
                'recurrence_pattern' => [
                    'daysOfWeek' => ['wednesday'],
                    'weekType' => 'odd'
                ],
                'default_due_time' => '23:59',
                'is_active' => true,
            ],
            [
                'name' => 'Genomgång och kontroll av reningsverk/oljeavskiljare',
                'description' => 'Genomgång och kontroll av reningsverk/oljeavskiljare',
                'interval_type' => 'weekly',
                'interval_value' => 2,
                'recurrence_pattern' => [
                    'daysOfWeek' => ['wednesday'],
                    'weekType' => 'odd'
                ],
                'default_due_time' => '23:59',
                'is_active' => true,
            ],
            [
                'name' => 'Avfettningspejling och vattenkontroll',
                'description' => 'Avfettningspejling och vattenkontroll',
                'interval_type' => 'weekly',
                'interval_value' => 2,
                'recurrence_pattern' => [
                    'daysOfWeek' => ['wednesday'],
                    'weekType' => 'odd'
                ],
                'default_due_time' => '23:59',
                'is_active' => true,
            ],
            [
                'name' => 'Rensa hängrännor',
                'description' => 'Rensa hängrännor (endast Hammarö)',
                'interval_type' => 'weekly',
                'interval_value' => 2,
                'recurrence_pattern' => [
                    'daysOfWeek' => ['wednesday'],
                    'weekType' => 'odd'
                ],
                'default_due_time' => '23:59',
                'is_active' => true,
            ],

            // GRÅ - Gårdsarbete (jämn vecka fredag)
            [
                'name' => 'Städa gården och ta bort löv',
                'description' => 'Städa gården och ta bort löv',
                'interval_type' => 'weekly',
                'interval_value' => 2,
                'recurrence_pattern' => [
                    'daysOfWeek' => ['friday'],
                    'weekType' => 'even'
                ],
                'default_due_time' => '23:59',
                'is_active' => true,
            ],
            [
                'name' => 'Sopa runt hela området',
                'description' => 'Sopa runt hela området',
                'interval_type' => 'weekly',
                'interval_value' => 2,
                'recurrence_pattern' => [
                    'daysOfWeek' => ['friday'],
                    'weekType' => 'even'
                ],
                'default_due_time' => '23:59',
                'is_active' => true,
            ],
            [
                'name' => 'Tömma dammsugare och rengöra runt dammsugplatser',
                'description' => 'Tömma dammsugare och rengöra runt dammsugplatser',
                'interval_type' => 'weekly',
                'interval_value' => 2,
                'recurrence_pattern' => [
                    'daysOfWeek' => ['friday'],
                    'weekType' => 'even'
                ],
                'default_due_time' => '23:59',
                'is_active' => true,
            ],
            [
                'name' => 'Allmän utvändig städning',
                'description' => 'Allmän utvändig städning',
                'interval_type' => 'weekly',
                'interval_value' => 2,
                'recurrence_pattern' => [
                    'daysOfWeek' => ['friday'],
                    'weekType' => 'even'
                ],
                'default_due_time' => '23:59',
                'is_active' => true,
            ],

            // Säsongsarbete
            [
                'name' => 'Gräsklippning (sommar)',
                'description' => 'Gräsklippning (sommar)',
                'interval_type' => 'weekly',
                'interval_value' => 1,
                'recurrence_pattern' => [
                    'daysOfWeek' => ['saturday']
                ],
                'default_due_time' => '23:59',
                'is_active' => true,
            ],
            [
                'name' => 'Snöskottning (vinter)',
                'description' => 'Snöskottning (vinter)',
                'interval_type' => 'weekly',
                'interval_value' => 1,
                'recurrence_pattern' => [
                    'daysOfWeek' => ['saturday']
                ],
                'default_due_time' => '23:59',
                'is_active' => true,
            ],

            // Automathall vid behov i högsäsong (ojämn vecka fredag)
            [
                'name' => 'Rengöring automathall (vid behov högsäsong)',
                'description' => 'Rengöring automathall (endast vid behov i högsäsong)',
                'interval_type' => 'weekly',
                'interval_value' => 2,
                'recurrence_pattern' => [
                    'daysOfWeek' => ['friday'],
                    'weekType' => 'odd'
                ],
                'default_due_time' => '23:59',
                'is_active' => true,
            ],
        ];

        foreach ($hammaroTasks as $taskData) {
            $taskData['station_id'] = $hammaroStation->id;
            Task::firstOrCreate(
                [
                    'station_id' => $taskData['station_id'],
                    'name' => $taskData['name']
                ],
                $taskData
            );
        }

        // ==========================================
        // VÅXNÄS SPECIFIKA UPPGIFTER
        // ==========================================

        $vaxnasTasks = [
            // Tekniskt underhåll & reningsverk (måndag och fredag)
            [
                'name' => 'Tekniskt underhåll och reningsverk',
                'description' => 'Tekniskt underhåll och rengöring av reningsverk',
                'interval_type' => 'weekly',
                'interval_value' => 1,
                'recurrence_pattern' => [
                    'daysOfWeek' => ['monday', 'friday']
                ],
                'default_due_time' => '23:59',
                'is_active' => true,
            ],

            // Snöskottning (vinter) - fredag
            [
                'name' => 'Snöskottning (vinter)',
                'description' => 'Snöskottning (vinter)',
                'interval_type' => 'weekly',
                'interval_value' => 1,
                'recurrence_pattern' => [
                    'daysOfWeek' => ['friday']
                ],
                'default_due_time' => '23:59',
                'is_active' => true,
            ],
        ];

        foreach ($vaxnasTasks as $taskData) {
            $taskData['station_id'] = $vaxnasStation->id;
            Task::firstOrCreate(
                [
                    'station_id' => $taskData['station_id'],
                    'name' => $taskData['name']
                ],
                $taskData
            );
        }
    }
}