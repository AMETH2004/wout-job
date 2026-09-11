<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class WoutActualiser extends Command
{
    protected $signature = 'wout:actualiser';

    protected $description = 'Cycle complet : scrape toutes les sources actives puis envoie les alertes dues. Seule commande à lancer manuellement (ou via le planificateur Windows).';

    public function handle(): int
    {
        $this->call('scraper:run');
        $this->call('alertes:envoyer');

        return self::SUCCESS;
    }
}
