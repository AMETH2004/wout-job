<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Cross-Origin Resource Sharing (CORS) Configuration
    |--------------------------------------------------------------------------
    |
    | Autorise le SPA Angular (ng serve, http://localhost:4200) à appeler
    | l'API. Authentification par token Bearer Sanctum : pas de cookies
    | cross-site, donc supports_credentials reste à false.
    |
    */

    'paths' => ['api/*', 'sanctum/csrf-cookie'],

    'allowed_methods' => ['*'],

    'allowed_origins' => [
        env('FRONTEND_URL', 'http://localhost:4200'),
    ],

    // Autorise n'importe quel port localhost en dev (ng serve peut basculer
    // de port si 4200 est déjà pris par un autre projet local).
    'allowed_origins_patterns' => [
        '#^http://(localhost|127\.0\.0\.1):\d+$#',
    ],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,

    'supports_credentials' => false,

];
