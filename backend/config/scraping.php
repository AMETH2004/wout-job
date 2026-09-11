<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Politesse du scraper
    |--------------------------------------------------------------------------
    |
    | User-Agent identifiable + délai minimum entre deux requêtes vers un
    | même site. `crawl_delay` par source doit refléter le robots.txt réel
    | du site (ex: offremplois.com impose Crawl-delay: 10).
    |
    */

    'user_agent' => env('SCRAPER_USER_AGENT', 'WoutJobBot/1.0'),

    'timeout' => 15,

    'crawl_delay_defaut' => 2,

    /*
    |--------------------------------------------------------------------------
    | Registre des scrapers
    |--------------------------------------------------------------------------
    |
    | slug de la source (table `sources`) => [classe, délai en secondes].
    | Ajouter une source = ajouter une classe qui implémente SourceScraper
    | + une ligne ici + une ligne dans le seeder SourceSeeder.
    |
    */

    'scrapers' => [
        'jobsenegal' => [
            'classe' => \App\Scraping\Scrapers\JobSenegalScraper::class,
            'crawl_delay' => 2,
        ],
        'offremplois' => [
            'classe' => \App\Scraping\Scrapers\OffremploisScraper::class,
            'crawl_delay' => 10,
        ],
    ],

];
