<?php

namespace App\Console\Commands;

use App\Models\Offre;
use App\Models\ScrapeRun;
use App\Models\Source;
use App\Scraping\HttpClientPoli;
use App\Scraping\OffreBrute;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ScraperRun extends Command
{
    protected $signature = 'scraper:run {source? : slug d\'une source précise (sinon toutes les sources actives)}';

    protected $description = 'Scrape une ou toutes les sources actives et met à jour la table offres.';

    public function handle(): int
    {
        $sources = $this->argument('source')
            ? Source::where('slug', $this->argument('source'))->where('actif', true)->get()
            : Source::where('actif', true)->get();

        if ($sources->isEmpty()) {
            $this->warn('Aucune source active à scraper.');

            return self::SUCCESS;
        }

        foreach ($sources as $source) {
            $this->scraperUneSource($source);
        }

        return self::SUCCESS;
    }

    private function scraperUneSource(Source $source): void
    {
        $config = config("scraping.scrapers.{$source->slug}");

        if (! $config) {
            $this->warn("Pas de scraper configuré pour « {$source->slug} », ignoré.");

            return;
        }

        $this->info("--- Scraping {$source->nom} ---");

        $run = ScrapeRun::create([
            'source_id' => $source->id,
            'demarre_at' => now(),
            'statut' => 'en_cours',
        ]);

        $trouvees = 0;
        $nouvelles = 0;
        $misesAJour = 0;
        $erreurs = [];

        try {
            $http = new HttpClientPoli($source->slug, $config['crawl_delay']);
            $classeScraper = $config['classe'];
            $scraper = new $classeScraper($http);

            foreach ($scraper->recupererOffres() as $offreBrute) {
                /** @var OffreBrute $offreBrute */
                $trouvees++;

                try {
                    $resultat = $this->upsertOffre($source, $offreBrute);
                    $nouvelles += $resultat === 'creee' ? 1 : 0;
                    $misesAJour += $resultat === 'maj' ? 1 : 0;
                } catch (\Throwable $e) {
                    $erreurs[] = "{$offreBrute->urlSource} : {$e->getMessage()}";
                }
            }

            // Grace period de 14 jours avant de considérer une offre non revue
            // comme expirée (les scrapers ne parcourent qu'un nombre limité de
            // pages par exécution, une offre plus ancienne peut être hors de
            // portée sans être réellement terminée).
            Offre::where('source_id', $source->id)
                ->where('statut', 'active')
                ->where('derniere_vue_at', '<', now()->subDays(14))
                ->update(['statut' => 'expiree']);

            $source->update(['dernier_scrape_at' => now()]);

            $run->update([
                'termine_at' => now(),
                'offres_trouvees' => $trouvees,
                'offres_nouvelles' => $nouvelles,
                'offres_maj' => $misesAJour,
                'erreurs' => $erreurs ? implode("\n", $erreurs) : null,
                'statut' => 'succes',
            ]);

            $this->info("{$source->nom} : {$trouvees} offre(s) vue(s), {$nouvelles} nouvelle(s), {$misesAJour} mise(s) à jour.");
        } catch (\Throwable $e) {
            $run->update([
                'termine_at' => now(),
                'offres_trouvees' => $trouvees,
                'offres_nouvelles' => $nouvelles,
                'offres_maj' => $misesAJour,
                'erreurs' => $e->getMessage(),
                'statut' => 'echec',
            ]);

            $this->error("Échec du scraping de {$source->nom} : {$e->getMessage()}");
        }
    }

    /**
     * @return 'creee'|'maj'|'inchangee'
     *
     * La classification est capturée par référence AVANT de sortir de
     * DB::transaction(). C'est nécessaire car `scout.after_commit = true`
     * fait courir la synchronisation Meilisearch en callback "after commit"
     * : si Meilisearch est injoignable, l'exception surgit après le commit
     * SQL (la ligne est bien enregistrée) mais avant que la valeur de retour
     * de la closure ne remonte à l'appelant. On logue cet échec de sync sans
     * le compter comme un échec de scraping — les données sont sauves,
     * seule l'indexation est en retard (rattrapable via `scout:import`).
     */
    private function upsertOffre(Source $source, OffreBrute $offreBrute): string
    {
        $donnees = $offreBrute->versTableau();
        $hash = sha1($donnees['titre'].'|'.$donnees['description_texte']);
        $classification = 'inchangee';

        try {
            DB::transaction(function () use ($source, $donnees, $hash, &$classification) {
                $offre = Offre::where('source_id', $source->id)
                    ->where('source_ref', $donnees['source_ref'])
                    ->first();

                if (! $offre) {
                    Offre::create(array_merge($donnees, [
                        'source_id' => $source->id,
                        'hash_contenu' => $hash,
                        'statut' => 'active',
                        'premiere_vue_at' => now(),
                        'derniere_vue_at' => now(),
                    ]));

                    $classification = 'creee';

                    return;
                }

                $inchangee = $offre->hash_contenu === $hash && $offre->statut === 'active';

                $offre->fill(array_merge($donnees, [
                    'hash_contenu' => $hash,
                    'statut' => 'active',
                    'derniere_vue_at' => now(),
                ]));
                $offre->save();

                $classification = $inchangee ? 'inchangee' : 'maj';
            });
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning(
                "[scraper:{$source->slug}] synchronisation recherche échouée pour {$donnees['url_source']}: {$e->getMessage()}"
            );
        }

        return $classification;
    }
}
