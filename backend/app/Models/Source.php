<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Source extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'actif' => 'boolean',
        'dernier_scrape_at' => 'datetime',
    ];

    public function offres(): HasMany
    {
        return $this->hasMany(Offre::class);
    }

    public function scrapeRuns(): HasMany
    {
        return $this->hasMany(ScrapeRun::class);
    }
}
