<?php

namespace App\Scraping\Scrapers;

use App\Scraping\Contracts\SourceScraper;
use App\Scraping\ExtractionHeuristiques as Extraction;
use App\Scraping\HttpClientPoli;
use App\Scraping\OffreBrute;
use Symfony\Component\DomCrawler\Crawler;

/**
 * jobsenegal.com — WordPress (thème famille "Pixwell"). Liste : <article
 * class="post-wrap"> avec titre dans .post-title-link, catégories dans
 * .cat-info-el. Pagination WordPress standard /page/N/.
 */
class JobSenegalScraper implements SourceScraper
{
    private const CATEGORIES = ['offre-demploi', 'stage'];

    private const MAX_PAGES = 3;

    public function __construct(private readonly HttpClientPoli $http) {}

    public function slug(): string
    {
        return 'jobsenegal';
    }

    public function recupererOffres(): iterable
    {
        foreach (self::CATEGORIES as $categorieSlug) {
            for ($page = 1; $page <= self::MAX_PAGES; $page++) {
                $url = $page === 1
                    ? "https://jobsenegal.com/category/{$categorieSlug}/"
                    : "https://jobsenegal.com/category/{$categorieSlug}/page/{$page}/";

                $liste = $this->http->get($url);
                if (! $liste) {
                    break;
                }

                $articles = $liste->filter('article.post-wrap');
                if ($articles->count() === 0) {
                    break;
                }

                foreach ($articles as $noeud) {
                    $offre = $this->extraireOffre(new Crawler($noeud));
                    if ($offre) {
                        yield $offre;
                    }
                }
            }
        }
    }

    private function extraireOffre(Crawler $article): ?OffreBrute
    {
        $lien = $article->filter('.post-title-link');
        if ($lien->count() === 0) {
            return null;
        }

        $titre = trim($lien->text(''));
        $url = $lien->attr('href');
        if (! $titre || ! $url) {
            return null;
        }
        $url = Extraction::urlAbsolue($url, 'https://jobsenegal.com');

        $categories = $article->filter('.cat-info-el')->each(fn (Crawler $n) => trim($n->text('')));

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
