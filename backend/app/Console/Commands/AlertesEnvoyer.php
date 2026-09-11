<?php

namespace App\Console\Commands;

use App\Mail\NouvellesOffresMail;
use App\Models\Offre;
use App\Models\RechercheSauvegardee;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class AlertesEnvoyer extends Command
{
    protected $signature = 'alertes:envoyer';

    protected $description = 'Envoie les alertes e-mail des recherches sauvegardées dues (quotidienne/hebdomadaire).';

    public function handle(): int
    {
        $recherches = RechercheSauvegardee::with('user')
            ->where('frequence_alerte', '!=', 'desactivee')
            ->get()
            ->filter(fn (RechercheSauvegardee $r) => $r->estDue());

        if ($recherches->isEmpty()) {
            $this->info('Aucune alerte due.');

            return self::SUCCESS;
        }

        foreach ($recherches as $recherche) {
            $depuis = $recherche->derniere_alerte_envoyee_at ?? $recherche->created_at;

            $offres = $this->offresCorrespondantes($recherche, $depuis);

            if ($offres->isNotEmpty()) {
                Mail::to($recherche->user->email)->send(new NouvellesOffresMail($recherche, $offres));
                $this->info("« {$recherche->nom} » ({$recherche->user->email}) : {$offres->count()} nouvelle(s) offre(s) envoyée(s).");
            } else {
                $this->line("« {$recherche->nom} » : rien de nouveau.");
            }

            // On marque la recherche comme vérifiée qu'il y ait eu un match ou
            // non, sinon une recherche qui ne matche rien resterait "due" en
            // permanence et on ne saurait plus jusqu'où on a déjà cherché.
            $recherche->update(['derniere_alerte_envoyee_at' => now()]);
        }

        return self::SUCCESS;
    }

    private function offresCorrespondantes(RechercheSauvegardee $recherche, \DateTimeInterface $depuis)
    {
        // Requête directe sur la table `offres` (pas Meilisearch) : les
        // alertes doivent fonctionner même si le moteur de recherche n'est
        // pas démarré — Meilisearch sert la recherche interactive (facettes,
        // pertinence), pas ce job de fond.
        return Offre::query()
            ->where('statut', 'active')
            ->where(function ($q) use ($depuis) {
                $q->where('date_publication', '>=', $depuis)
                    ->orWhere('premiere_vue_at', '>=', $depuis);
            })
            ->when($recherche->lieu, fn ($q) => $q->where('lieu', $recherche->lieu))
            ->when($recherche->type_contrat, fn ($q) => $q->where('type_contrat', $recherche->type_contrat))
            ->when($recherche->categorie, fn ($q) => $q->where('categorie', $recherche->categorie))
            ->when($recherche->mots_cles, function ($q) use ($recherche) {
                $mot = $recherche->mots_cles;
                $q->where(function ($sub) use ($mot) {
                    $sub->where('titre', 'like', "%{$mot}%")
                        ->orWhere('description_texte', 'like', "%{$mot}%");
                });
            })
            ->orderByDesc('date_publication')
            ->limit(20)
            ->get();
    }
}
