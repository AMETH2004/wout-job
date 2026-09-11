<?php

namespace App\Http\Controllers;

use App\Models\Favori;
use Illuminate\Http\Request;

class FavoriController extends Controller
{
    public function index(Request $request)
    {
        return response()->json(
            $request->user()->favoris()->with('offre.source:id,nom,slug')->latest()->get()
        );
    }

    public function store(Request $request)
    {
        $data = $request->validate(['offre_id' => 'required|exists:offres,id']);

        $favori = Favori::firstOrCreate([
            'user_id' => $request->user()->id,
            'offre_id' => $data['offre_id'],
        ]);

        return response()->json(['message' => 'Ajouté aux favoris.', 'data' => $favori], 201);
    }

    public function destroy(Request $request, int $offreId)
    {
        Favori::where('user_id', $request->user()->id)
            ->where('offre_id', $offreId)
            ->delete();

        return response()->json(['message' => 'Retiré des favoris.']);
    }
}
