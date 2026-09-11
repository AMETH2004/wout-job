# WoutJob — agrégateur d'offres de stages/emplois (Sénégal)

Scrape plusieurs sites d'offres sénégalais, indexe le résultat dans
Meilisearch et l'expose via une API Laravel + une interface Angular de
recherche à facettes. Comptes utilisateurs, favoris et recherches
sauvegardées avec alertes e-mail.

- **backend/** — API REST Laravel 13 + Sanctum + scraping + Laravel Scout/Meilisearch (MySQL)
- **frontend/** — SPA Angular 22 (standalone, Bootstrap 5)

## Prérequis

PHP 8.3+, Composer, MySQL 8, Node 20+, [Meilisearch](https://www.meilisearch.com/) (binaire local, voir plus bas).

## Démarrage

### 1. Meilisearch

Binaire Windows téléchargé dans `C:\laragon\bin\meilisearch\meilisearch.exe`
(non versionné). Le lancer avec la clé définie dans `.env` :

```bash
meilisearch --http-addr 127.0.0.1:7700 --master-key <MEILISEARCH_KEY>
```

### 2. Backend

```bash
cd backend
composer install
cp .env.example .env          # DB_DATABASE=wout_job, MEILISEARCH_KEY à renseigner
php artisan key:generate
php artisan migrate --seed     # crée les tables + sources + un compte de démo
php artisan scout:sync-index-settings   # applique les attributs filtrables/triables sur l'index Meilisearch
php artisan serve              # http://127.0.0.1:8000
```

Compte créé par le seeder : `demo@wout-job.test` / `password`.

### 3. Scraper les offres

**Le scraping ne se déclenche jamais depuis l'API/l'UI** — uniquement en
ligne de commande, pour éviter tout déclenchement accidentel ou abusif :

```bash
php artisan wout:actualiser     # scrape toutes les sources actives + envoie les alertes dues
# ou, plus finement :
php artisan scraper:run jobsenegal
php artisan scraper:run offremplois
php artisan alertes:envoyer
```

À automatiser plus tard via le planificateur de tâches Windows si souhaité
(la commande est idempotente : elle met à jour les offres existantes plutôt
que de les dupliquer).

### 4. Frontend

```bash
cd frontend
npm install
npm start                      # http://localhost:4200
```

## Sources scrapées

| Source | Statut | Notes |
|---|---|---|
| jobsenegal.com | ✅ actif | WordPress, `<article class="post-wrap">` |
| offremplois.com | ✅ actif | WordPress, respecte le `Crawl-delay: 10` de son robots.txt |
| senjob.com | phase 2 | structure à ré-investiguer |
| emploidakar.com | phase 2 | WP Job Manager, liste chargée en AJAX côté client |
| emploisenegal.com | écarté | bloque les requêtes automatisées (Cloudflare), malgré un robots.txt qui autorise explicitement l'indexation |

### Éthique du scraping

- User-Agent identifiable (`SCRAPER_USER_AGENT` dans `.env`), avec contact
- Délai minimum entre requêtes par source (`config/scraping.php`), aligné sur le `Crawl-delay` déclaré quand il existe
- Seules les pages publiques non interdites par `robots.txt` sont visitées
- On affiche un extrait + un lien vers l'annonce originale — pas de republication intégrale (posture d'agrégateur/moteur de recherche)
- `entreprise` et `lieu` sont extraits par heuristique sur le texte (ces sites sont des blogs, pas de vrais job boards à champs structurés) : ils peuvent être approximatifs ou absents — voir `app/Scraping/ExtractionHeuristiques.php`

## Architecture scraping

Un scraper = une classe qui implémente `App\Scraping\Contracts\SourceScraper`
(voir `JobSenegalScraper`, `OffremploisScraper`), utilise `HttpClientPoli`
pour toute requête HTTP (politesse centralisée) et s'enregistre dans
`config/scraping.php`. `php artisan scraper:run` upsert les résultats dans
`offres` (dédoublonnage par `source_id` + URL), calcule un hash de contenu
pour ne ré-indexer que ce qui a changé, et journalise chaque exécution dans
`scrape_runs` (visible côté UI sur la page **Sources**).
