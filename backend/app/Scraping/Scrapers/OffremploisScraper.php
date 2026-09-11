<?php

namespace App\Scraping\Scrapers;

use App\Scraping\Contracts\SourceScraper;
use App\Scraping\ExtractionHeuristiques as Extraction;
use App\Scraping\HttpClientPoli;
use App\Scraping\OffreBrute;
use Symfony\Component\DomCrawler\Crawler;

/**
 * offremplois.com — WordPress (même famille de thème que jobsenegal.com,
 * classes différentes). Liste : <div class="p-wrap"> avec titre dans
 * .entry-title a, catégories dans .cat-info-el. Le site impose
 * Crawl-delay: 10 dans son robots.txt (voir config/scraping.php).
 */
class OffremploisScraper implements SourceScraper
{
    private const CATEGORIES = ['offres-de-stages', 'emplois'];

    private const MAX_PAGES = 3;

    public function __construct(private readonly HttpClientPoli $http) {}

    public function slug(): string
    {
        return 'offremplois';
    }

    public function recupererOffres(): iterable
    {
        foreach (self::CATEGORIES as $categorieSlug) {
            for ($page = 1; $page <= self::MAX_PAGES; $page++) {
                $url = $page === 1
                    ? "https://offremplois.com/category/{$categorieSlug}/"
                    : "https://offremplois.com/category/{$categorieSlug}/page/{$page}/";

                $liste = $this->http->get($url);
                if (! $liste) {
                    break;
                }

                $items = $liste->filter('div.p-wrap');
                if ($items->count() === 0) {
                    break;
                }

                foreach ($items as $noeud) {
                    $offre = $this->extraireOffre(new Crawler($noeud));
                    if ($offre) {
                        yield $offre;
                    }
                }
            }
        }
    }

    private function extraireOffre(Crawler $item): ?OffreBrute
    {
        $lien = $item->filter('.entry-title a');
        if ($lien->count() === 0) {
            return null;
        }

        $titre = trim($lien->text(''));
        $url = $lien->attr('href');
        if (! $titre || ! $url) {
            return null;
        }
        $url = Extraction::urlAbsolue($url, 'https://offremplois.com');

        $categories = $item->filter('.cat-info-el')->each(fn (Crawler $n) => trim($n->text('')));

        $page = $this->http->get($url);
        $descriptionTexte = $page ? Extraction::contenuTexte($page) : '';

        return new OffreBrute(
            sourceRef: $url,
            titre: $titre,
            urlSource: $url,
            entreprise: Extraction::entrepriseDepuisTitre($titre),
            lieu: Extraction::lieuDepuisTexte($titre, $descriptionTexte),
            typeContrat: Extraction::typeContratDepuisCategories($categories),
            categorie: $categories[0] ?? null,
            descriptionHtml: $page ? Extraction::contenuHtml($page) : null,
            descriptionTexte: $descriptionTexte,
            datePublication: $page ? Extraction::datePublication($page) : null,
        );
    }
}
