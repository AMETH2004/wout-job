<?php

namespace App\Http\Controllers;

use App\Models\Offre;
use App\Models\Source;

class StatsController extends Controller
{
    public function index()
    {
        return response()->json([
            'offres_actives' => Offre::where('statut', 'active')->count(),
            'offres_par_type' => Offre::where('statut', 'active')
                ->selectRaw('type_contrat, count(*) as total')
                ->groupBy('type_contrat')
                ->pluck('total', 'type_contrat'),
            'offres_par_source' => Offre::where('statut', 'active')
                ->join('sources', 'sources.id', '=', 'offres.source_id')
                ->selectRaw('sources.nom, count(*) as total')
                ->groupBy('sources.nom')
                ->pluck('total', 'sources.nom'),
            'sources_actives' => Source::where('actif', true)->count(),
        ]);
    }
}
