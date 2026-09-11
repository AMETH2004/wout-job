<?php

namespace Database\Seeders;

use App\Models\Source;
use Illuminate\Database\Seeder;

class SourceSeeder extends Seeder
{
    public function run(): void
    {
        $sources = [
            ['nom' => 'JobSenegal', 'slug' => 'jobsenegal', 'url_base' => 'https://jobsenegal.com', 'actif' => true],
            ['nom' => 'Offremplois', 'slug' => 'offremplois', 'url_base' => 'https://offremplois.com', 'actif' => true],
            // Phase 2 : structure pas encore fiabilisée (voir app/Scraping) — présentes
            // dans la table pour référence mais inactives tant qu'aucun scraper n'existe.
            ['nom' => 'Senjob', 'slug' => 'senjob', 'url_base' => 'https://senjob.com', 'actif' => false],
            ['nom' => 'EmploiDakar', 'slug' => 'emploidakar', 'url_base' => 'https://www.emploidakar.com', 'actif' => false],
        ];

        foreach ($sources as $s) {
            Source::updateOrCreate(['slug' => $s['slug']], $s);
        }
    }
}
