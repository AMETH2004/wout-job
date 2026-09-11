<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Laravel\Scout\Searchable;

class Offre extends Model
{
    use Searchable;

    protected $guarded = ['id'];

    protected $casts = [
        'date_publication' => 'date',
        'date_limite' => 'date',
        'premiere_vue_at' => 'datetime',
        'derniere_vue_at' => 'datetime',
    ];

    public function source(): BelongsTo
    {
        return $this->belongsTo(Source::class);
    }

    public function favoris(): HasMany
    {
        return $this->hasMany(Favori::class);
    }

    /**
     * Seules les offres actives sont indexées : une offre expirée disparaît
     * de la recherche sans qu'on ait besoin de la supprimer de la base.
     */
    public function shouldBeSearchable(): bool
    {
        return $this->statut === 'active';
    }

    /**
     * @return array<string, mixed>
     */
    public function toSearchableArray(): array
    {
        return [
            'id' => $this->id,
            'titre' => $this->titre,
            'entreprise' => $this->entreprise,
            'lieu' => $this->lieu,
            'type_contrat' => $this->type_contrat,
            'categorie' => $this->categorie,
            'description_texte' => $this->description_texte,
            'source_slug' => $this->source?->slug,
            'statut' => $this->statut,
            // timestamp pour le tri chronologique (sortableAttributes dans config/scout.php)
            'date_publication' => $this->date_publication?->timestamp ?? $this->created_at->timestamp,
        ];
    }
}
