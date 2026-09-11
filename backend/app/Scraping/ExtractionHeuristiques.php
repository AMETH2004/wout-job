<?php

namespace App\Scraping;

use Carbon\Carbon;
use Carbon\CarbonInterface;
use Symfony\Component\DomCrawler\Crawler;

/**
 * Extraction générique réutilisée par plusieurs scrapers WordPress : ces
 * sites n'exposent pas de champs structurés (entreprise/lieu/date) comme le
 * ferait un vrai job board, seulement un article de blog. On s'appuie sur
 * des conventions largement répandues (balisage schema.org posé par les
 * plugins SEO, ex Yoast/RankMath) plutôt que sur des classes propres à un
 * thème — donc réutilisable si une future source partage la même stack.
 *
 * Les champs "entreprise" et "lieu" restent des heuristiques sur le texte
 * (titre + description) : ils peuvent être approximatifs ou absents.
 */
class ExtractionHeuristiques
{
    /** Villes sénégalaises courantes recherchées dans le texte pour deviner le lieu. */
    private const VILLES = [
        'Dakar', 'Thiès', 'Saint-Louis', 'Ziguinchor', 'Kaolack', 'Touba',
        'Rufisque', 'Mbour', 'Louga', 'Diourbel', 'Tambacounda', 'Kolda',
        'Fatick', 'Kédougou', 'Matam', 'Sédhiou', 'Kaffrine', 'Podor',
        'Pikine', 'Guédiawaye', 'Dakar Plateau',
    ];

    public static function datePublication(Crawler $page): ?CarbonInterface
    {
        $noeud = $page->filter('meta[property="article:published_time"]');
        if ($noeud->count() === 0) {
            return null;
        }

        try {
            return Carbon::parse($noeud->attr('content'));
        } catch (\Throwable) {
            return null;
        }
    }

    /**
     * Texte brut du corps de l'article (paragraphes uniquement, sans les
     * scripts publicitaires souvent injectés dans le contenu).
     */
    public static function contenuTexte(Crawler $page): string
    {
        $corps = $page->filter('[itemprop="articleBody"]');
        if ($corps->count() === 0) {
            return '';
        }

        $blocs = $corps->filter('p, li, h2, h3')->each(
            fn (Crawler $n) => trim($n->text(''))
        );

        $blocs = array_filter($blocs, fn (string $b) => $b !== '');

        return $blocs ? implode("\n", $blocs) : trim($corps->text(''));
    }

    public static function contenuHtml(Crawler $page): string
    {
        $corps = $page->filter('[itemprop="articleBody"]');

        return $corps->count() ? $corps->html('') : '';
    }

    /**
     * Devine l'entreprise à partir du titre : « Nogora SA recrute... »,
     * « La BOAD lance son programme... ». Retourne null si aucun motif reconnu.
     */
    public static function entrepriseDepuisTitre(string $titre): ?string
    {
        if (preg_match('/^(.{2,60}?)\s+(?:recrute|recherche|lance)\b/ui', $titre, $m)) {
            return trim($m[1]);
        }

        return null;
    }

    /**
     * Devine le lieu en cherchant une ville sénégalaise connue dans le texte
     * (titre puis description). Approximatif par nature.
     */
    public static function lieuDepuisTexte(string ...$textes): ?string
    {
        $texte = implode(' ', $textes);

        foreach (self::VILLES as $ville) {
            if (mb_stripos($texte, $ville) !== false) {
                return $ville;
            }
        }

        return null;
    }

    public static function typeContratDepuisCategories(array $categories): string
    {
        $texte = mb_strtolower(implode(' ', $categories));

        return match (true) {
            str_contains($texte, 'alternance') => 'alternance',
            str_contains($texte, 'stage') => 'stage',
            str_contains($texte, 'emploi'), str_contains($texte, 'recrutement') => 'emploi',
            default => 'autre',
        };
    }
}
