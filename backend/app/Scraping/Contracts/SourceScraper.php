<?php

namespace App\Scraping\Contracts;

use App\Scraping\OffreBrute;

interface SourceScraper
{
    /**
     * Doit correspondre au `slug` de la ligne `sources` associée.
     */
    public function slug(): string;

    /**
     * Parcourt les pages de listing du site (en gérant sa propre pagination)
     * et, pour chaque annonce, va chercher la page de détail. Chaque requête
     * HTTP doit passer par HttpClientPoli pour respecter le délai imposé.
     *
     * @return iterable<OffreBrute>
     */
    public function recupererOffres(): iterable;
}
