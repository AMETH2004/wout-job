<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ScrapeRun extends Model
{
    protected $table = 'scrape_runs';

    protected $guarded = ['id'];

    protected $casts = [
        'demarre_at' => 'datetime',
        'termine_at' => 'datetime',
    ];

    public function source(): BelongsTo
    {
        return $this->belongsTo(Source::class);
    }
}
