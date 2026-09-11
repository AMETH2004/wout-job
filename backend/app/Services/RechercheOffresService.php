<?php

namespace App\Services;

use Meilisearch\Client;

/**
 * Interroge directement le client Meilisearch (plutôt que la façade fluide
 * de Scout) pour récupérer, en plus des résultats, la distribution de
 * facettes (compteurs par lieu/type de contrat/catégorie/source) que Scout
 * n'expose pas nativement.
 */
class RechercheOffresService
{
    private Client $client;

    public function __construct()
    {
        $this->client = new Client(config('scout.meilisearch.host'), config('scout.meilisearch.key'));
    }

    /**
     * @param  array<string, string>  $filtres  lieu, type_contrat, categorie, source_slug
     */
    public function rechercher(
        string $q = '',
        array $filtres = [],
        int $page = 1,
        int $parPage = 20,
        string $tri = 'pertinence',
    ): array {
        $index = $this->client->index('offres');

        $expressions = ['statut = active'];
        foreach ($filtres as $champ => $valeur) {
            if ($valeur !== null && $valeur !== '') {
                $expressions[] = sprintf('%s = "%s"', $champ, addslashes($valeur));
            }
        }

        $options = [
            'filter' => implode(' AND ', $expressions),
            'facets' => ['lieu', 'type_contrat', 'categorie', 'source_slug'],
            'offset' => ($page - 1) * $parPage,
            'limit' => $parPage,
        ];

        if ($tri === 'recent') {
            $options['sort'] = ['date_publication:desc'];
        }

        $resultats = $index->search($q ?: null, $options);

        return [
            'donnees' => $resultats->getHits(),
            'total' => $resultats->getEstimatedTotalHits(),
            'page' => $page,
            'par_page' => $parPage,
            'facettes' => $resultats->getFacetDistribution() ?? [],
        ];
    }
}
