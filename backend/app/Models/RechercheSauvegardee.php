<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RechercheSauvegardee extends Model
{
    protected $table = 'recherches_sauvegardees';

    protected $guarded = ['id'];

    protected $casts = [
        'derniere_alerte_envoyee_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * L'alerte est due si sa fréquence n'est pas désactivée et que le délai
     * correspondant s'est écoulé depuis le dernier envoi (jamais envoyée = due).
     */
    public function estDue(): bool
    {
        if ($this->frequence_alerte === 'desactivee') {
            return false;
        }

        if (! $this->derniere_alerte_envoyee_at) {
            return true;
        }

        $intervalle = $this->frequence_alerte === 'quotidienne' ? 1 : 7;

        return $this->derniere_alerte_envoyee_at->diffInDays(now()) >= $intervalle;
    }
}
