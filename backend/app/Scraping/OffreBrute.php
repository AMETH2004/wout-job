<?php

namespace App\Scraping;

use Carbon\CarbonInterface;

/**
 * Offre telle qu'extraite d'un site source, avant upsert en base.
 * `source_ref` doit être stable et unique pour ce site (on utilise l'URL de
 * l'annonce, qui ne change pas d'un scrape à l'autre).
 */
class OffreBrute
{
    public function __construct(
        public readonly string $sourceRef,
        public readonly string $titre,
        public readonly string $urlSource,
        public readonly ?string $entreprise = null,
        public readonly ?string $lieu = null,
        public readonly string $typeContrat = 'autre',
        public readonly ?string $categorie = null,
        public readonly ?string $descriptionHtml = null,
        public readonly ?string $descriptionTexte = null,
        public readonly ?CarbonInterface $datePublication = null,
        public readonly ?CarbonInterface $dateLimite = null,
        public readonly ?string $urlImage = null,
    ) {}

    public function versTableau(): array
    {
        return [
            'source_ref' => $this->sourceRef,
            'titre' => $this->titre,
            'entreprise' => $this->entreprise,
            'lieu' => $this->lieu,
            'type_contrat' => $this->typeContrat,
            'categorie' => $this->categorie,
            'description_html' => $this->descriptionHtml,
            'description_texte' => $this->descriptionTexte,
            'date_publication' => $this->datePublication,
            'date_limite' => $this->dateLimite,
            'url_source' => $this->urlSource,
            'url_image' => $this->urlImage,
        ];
    }
}
