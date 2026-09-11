<?php

namespace App\Http\Controllers;

use App\Models\Offre;
use App\Services\RechercheOffresService;
use Illuminate\Http\Request;

class OffreController extends Controller
{
    public function index(Request $request, RechercheOffresService $recherche)
    {
        $resultat = $recherche->rechercher(
            q: (string) $request->string('q'),
            filtres: array_filter([
                'lieu' => $request->string('lieu')->value(),
                'type_contrat' => $request->string('type_contrat')->value(),
                'categorie' => $request->string('categorie')->value(),
                'source_slug' => $request->string('source')->value(),
            ]),
            page: max(1, $request->integer('page', 1)),
            parPage: min(50, $request->integer('par_page', 20)),
            tri: $request->string('tri', 'pertinence')->value(),
        );

        return response()->json($resultat);
    }

    public function show(Offre $offre)
    {
        return response()->json($offre->load('source:id,nom,slug'));
    }
}
