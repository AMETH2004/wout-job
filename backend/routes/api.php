<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\FavoriController;
use App\Http\Controllers\OffreController;
use App\Http\Controllers\RechercheSauvegardeeController;
use App\Http\Controllers\SourceController;
use App\Http\Controllers\StatsController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API WoutJob — agrégateur d'offres de stages/emplois
|--------------------------------------------------------------------------
| Lecture (offres/sources/stats) publique. Favoris et recherches
| sauvegardées nécessitent un compte (Sanctum). Le scraping ne se
| déclenche jamais via HTTP : uniquement `php artisan scraper:run`
| / `wout:actualiser`.
*/

// --- Public ---
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::get('/offres', [OffreController::class, 'index']);
Route::get('/offres/{offre}', [OffreController::class, 'show']);
Route::get('/sources', [SourceController::class, 'index']);
Route::get('/stats', [StatsController::class, 'index']);

// --- Authentifié ---
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'me']);

    Route::apiResource('recherches-sauvegardees', RechercheSauvegardeeController::class)
        ->except(['show']);

    Route::get('/favoris', [FavoriController::class, 'index']);
    Route::post('/favoris', [FavoriController::class, 'store']);
    Route::delete('/favoris/{offreId}', [FavoriController::class, 'destroy']);
});
