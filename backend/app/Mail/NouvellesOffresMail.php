<?php

namespace App\Mail;

use App\Models\RechercheSauvegardee;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class NouvellesOffresMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    /**
     * @param  Collection  $offres  Offres nouvelles correspondant à la recherche sauvegardée
     */
    public function __construct(
        public readonly RechercheSauvegardee $recherche,
        public readonly Collection $offres,
    ) {}

    public function build(): self
    {
        return $this
            ->subject("WoutJob — {$this->offres->count()} nouvelle(s) offre(s) pour « {$this->recherche->nom} »")
            ->view('emails.nouvelles-offres');
    }
}
