<?php

namespace Database\Seeders;

use App\Models\InventoryItem;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class InventoryItemSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $items = [
            // Rengöringsartiklar
            ['name' => 'Vattenslang', 'description' => 'Vattenslang för rengöring', 'unit' => 'pcs', 'default_reorder_level' => 1],
            ['name' => 'Spolmunstycke', 'description' => 'Munstycke för vattenslang', 'unit' => 'pcs', 'default_reorder_level' => 2],
            ['name' => 'Städborste', 'description' => 'Borste för rengöring', 'unit' => 'pcs', 'default_reorder_level' => 3],
            ['name' => 'Diskmedel', 'description' => 'Flytande diskmedel', 'unit' => 'liters', 'default_reorder_level' => 2],
            ['name' => 'Golvrengöringsmedel', 'description' => 'Rengöringsmedel för golv', 'unit' => 'liters', 'default_reorder_level' => 2],
            ['name' => 'Fönsterputsmedel', 'description' => 'Medel för fönsterputsning', 'unit' => 'liters', 'default_reorder_level' => 1],
            ['name' => 'Allrengöring', 'description' => 'Universalrengöring', 'unit' => 'liters', 'default_reorder_level' => 2],
            ['name' => 'Toalettpapper', 'description' => 'Toalettpapper', 'unit' => 'pcs', 'default_reorder_level' => 20],
            ['name' => 'Handpapper', 'description' => 'Pappershanddukar', 'unit' => 'pcs', 'default_reorder_level' => 10],
            ['name' => 'Tvål', 'description' => 'Flytande handtvål', 'unit' => 'liters', 'default_reorder_level' => 3],
            
            // Skyddsutrustning
            ['name' => 'Handskar', 'description' => 'Rengöringshandskar', 'unit' => 'pcs', 'default_reorder_level' => 10],
            ['name' => 'Förkläde', 'description' => 'Skyddsförkläde', 'unit' => 'pcs', 'default_reorder_level' => 5],
            
            // Sophantering
            ['name' => 'Soppåsar', 'description' => 'Soppåsar för allmänt avfall', 'unit' => 'pcs', 'default_reorder_level' => 50],
            ['name' => 'Återvinningspåsar', 'description' => 'Påsar för återvinning', 'unit' => 'pcs', 'default_reorder_level' => 20],
            
            // Verktyg och utrustning
            ['name' => 'Moppar', 'description' => 'Moppar för golvtorkning', 'unit' => 'pcs', 'default_reorder_level' => 3],
            ['name' => 'Mopptrasa', 'description' => 'Ersättningstrasa för mopp', 'unit' => 'pcs', 'default_reorder_level' => 5],
            ['name' => 'Hink', 'description' => 'Hink för vatten och rengöring', 'unit' => 'pcs', 'default_reorder_level' => 2],
            ['name' => 'Skrapa', 'description' => 'Isckrapa för vinterunderhåll', 'unit' => 'pcs', 'default_reorder_level' => 2],
            ['name' => 'Snöskyfffel', 'description' => 'Skyfffel för snöröjning', 'unit' => 'pcs', 'default_reorder_level' => 2],
            ['name' => 'Dammsugarpåsar', 'description' => 'Påsar för dammsugare', 'unit' => 'pcs', 'default_reorder_level' => 10],
            
            // Specialartiklar
            ['name' => 'Väggsalt', 'description' => 'Salt för vintervägar', 'unit' => 'kg', 'default_reorder_level' => 100],
            ['name' => 'Sprayflaska', 'description' => 'Sprayflaska för rengöringsmedel', 'unit' => 'pcs', 'default_reorder_level' => 3],
            ['name' => 'Mikrofibertrasa', 'description' => 'Mikrofibertrasa för rengöring', 'unit' => 'pcs', 'default_reorder_level' => 10],
            ['name' => 'Glampor LED', 'description' => 'LED-lampor för belysning', 'unit' => 'pcs', 'default_reorder_level' => 5],
            ['name' => 'Batterier AA', 'description' => 'AA-batterier för utrustning', 'unit' => 'pcs', 'default_reorder_level' => 20],
        ];

        foreach ($items as $item) {
            InventoryItem::firstOrCreate(
                ['name' => $item['name']],
                $item
            );
        }
    }
}
