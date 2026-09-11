<?php

namespace App\Http\Controllers;

use App\Models\RechercheSauvegardee;
use Illuminate\Http\Request;

class RechercheSauvegardeeController extends Controller
{
    public function index(Request $request)
    {
        return response()->json(
            $request->user()->recherchesSauvegardees()->latest()->get()
        );
    }

    public function store(Request $request)
    {
        $data = $request->validate($this->regles());

        $recherche = $request->user()->recherchesSauvegardees()->create($data);

        return response()->json([
            'message' => 'Recherche sauvegardée.',
            'data' => $recherche,
        ], 201);
    }

    public function update(Request $request, RechercheSauvegardee $rechercheSauvegardee)
    {
        $this->autoriser($request, $rechercheSauvegardee);

        $data = $request->validate($this->regles(sometimes: true));
        $rechercheSauvegardee->update($data);

        return response()->json([
            'message' => 'Recherche mise à jour.',
            'data' => $rechercheSauvegardee,
        ]);
    }

    public function destroy(Request $request, RechercheSauvegardee $rechercheSauvegardee)
    {
        $this->autoriser($request, $rechercheSauvegardee);
        $rechercheSauvegardee->delete();

        return response()->json(['message' => 'Recherche supprimée.']);
    }

    private function regles(bool $sometimes = false): array
    {
        $prefixe = $sometimes ? 'sometimes|' : 'required|';

        return [
            'nom' => $prefixe.'string|max:255',
            'mots_cles' => 'nullable|string|max:255',
            'lieu' => 'nullable|string|max:255',
            'type_contrat' => 'nullable|in:emploi,stage,alternance,autre',
            'categorie' => 'nullable|string|max:255',
            'frequence_alerte' => 'sometimes|in:quotidienne,hebdomadaire,desactivee',
        ];
    }

    private function autoriser(Request $request, RechercheSauvegardee $recherche): void
    {
        abort_unless($recherche->user_id === $request->user()->id, 403);
    }
}
