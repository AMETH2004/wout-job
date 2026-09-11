<?php

namespace App\Http\Controllers;

use App\Models\Source;

class SourceController extends Controller
{
    /**
     * Transparence sur le scraping : dernière exécution + volumétrie par
     * source, et historique des dernières exécutions.
     */
    public function index()
    {
        $sources = Source::withCount(['offres' => fn ($q) => $q->where('statut', 'active')])
            ->with(['scrapeRuns' => fn ($q) => $q->latest('demarre_at')->limit(5)])
            ->orderBy('nom')
            ->get();

        return response()->json($sources);
    }
}
