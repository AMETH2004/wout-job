<?php

namespace App\Scraping;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Symfony\Component\DomCrawler\Crawler;

/**
 * Point de passage unique pour toute requête HTTP sortante des scrapers :
 * User-Agent identifiable, délai minimum entre deux requêtes (respect du
 * Crawl-delay du site cible), une tentative de retry, et log des échecs
 * sans jamais faire planter le scraper appelant.
 */
class HttpClientPoli
{
    private float $dernierAppelAt = 0.0;

    public function __construct(
        private readonly string $sourceSlug,
        private readonly int $crawlDelaySecondes,
    ) {}

    /**
     * Récupère une page et la renvoie sous forme de Crawler (DOM parsable via
     * sélecteurs CSS), ou null si la requête échoue après une tentative de retry.
     */
    public function get(string $url): ?Crawler
    {
        $this->respecterDelai();

        $reponse = $this->requeter($url);

        if (! $reponse || $reponse->failed()) {
            $this->respecterDelai(force: true);
            $reponse = $this->requeter($url);
        }

        if (! $reponse || $reponse->failed()) {
            Log::warning("[scraper:{$this->sourceSlug}] échec de récupération", [
                'url' => $url,
                'status' => $reponse?->status(),
            ]);

            return null;
        }

        return new Crawler((string) $reponse->body(), $url);
    }

    private function requeter(string $url): ?\Illuminate\Http\Client\Response
    {
        try {
            return Http::withHeaders(['User-Agent' => config('scraping.user_agent')])
                ->timeout(config('scraping.timeout', 15))
                ->get($url);
        } catch (\Throwable $e) {
            Log::warning("[scraper:{$this->sourceSlug}] exception réseau", [
                'url' => $url,
                'message' => $e->getMessage(),
            ]);

            return null;
        }
    }

    private function respecterDelai(bool $force = false): void
    {
        $ecoule = microtime(true) - $this->dernierAppelAt;
        $attente = $this->crawlDelaySecondes - $ecoule;

        if ($force || $attente > 0) {
            usleep((int) max($attente, 0.5) * 1_000_000);
        }

        $this->dernierAppelAt = microtime(true);
    }
}
