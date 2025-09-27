<?php

namespace Database\Seeders;

use App\Models\TaskTemplate;
use Illuminate\Database\Seeder;

class TaskTemplateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $templates = [
            ['name' => 'Gräsklippning', 'description' => 'Klippa gräs runt stationen'],
            ['name' => 'Rensning av mossa', 'description' => 'Ta bort mossa från ytor'],
            ['name' => 'Sopning utomhus', 'description' => 'Sopa och städa utomhusområdet'],
            ['name' => 'Ogräsrensning', 'description' => 'Ta bort ogräs från rabatter och stenläggning'],
            ['name' => 'Snöskottning', 'description' => 'Skotta snö vid entréer och gångvägar'],
            ['name' => 'Fönsterputsning', 'description' => 'Putsa fönster och glaspartier'],
            ['name' => 'Golvmoppning', 'description' => 'Moppa golv i olika områden'],
            ['name' => 'Dammsugning', 'description' => 'Dammsuga mattor och textilier'],
            ['name' => 'Toalettrengöring', 'description' => 'Grundlig rengöring av toaletter'],
            ['name' => 'Spegelputsning', 'description' => 'Putsa speglar och reflekterande ytor'],
            ['name' => 'Kontroll av belysning', 'description' => 'Kontrollera att all belysning fungerar'],
            ['name' => 'Byte av lampor', 'description' => 'Byta trasiga lampor och lysrör'],
            ['name' => 'Lagning av småsaker', 'description' => 'Mindre reparationer och justeringar'],
            ['name' => 'Kontroll av säkerhetsutrustning', 'description' => 'Kontrollera brandsläckare, första hjälpen etc.'],
            ['name' => 'Organisering av förråd', 'description' => 'Ordna och organisera förråd och lager'],
            ['name' => 'Inventering', 'description' => 'Räkna och kontrollera lager'],
            ['name' => 'Sophantering', 'description' => 'Tömma sopor och återvinning'],
            ['name' => 'Specialrengöring', 'description' => 'Djuprengöring av specifika områden'],
        ];

        foreach ($templates as $template) {
            TaskTemplate::firstOrCreate(
                ['name' => $template['name']],
                $template
            );
        }
    }
}
