<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>Nouvelles offres</title>
</head>
<body style="font-family: system-ui, sans-serif; color: #1f2937; max-width: 640px; margin: 0 auto;">
    <h2 style="color:#111827;">Nouvelles offres pour « {{ $recherche->nom }} »</h2>
    <p style="color:#6b7280;">
        {{ $offres->count() }} nouvelle(s) offre(s) correspondant à votre recherche sauvegardée
        @if($recherche->mots_cles) « {{ $recherche->mots_cles }} » @endif
        @if($recherche->lieu) à {{ $recherche->lieu }} @endif
        @if($recherche->type_contrat) ({{ $recherche->type_contrat }}) @endif
        .
    </p>

    @foreach($offres as $offre)
        <div style="padding:12px 0; border-bottom:1px solid #e5e7eb;">
            <div style="font-weight:600;">{{ $offre->titre }}</div>
            <div style="color:#6b7280; font-size:14px;">
                @if($offre->entreprise) {{ $offre->entreprise }} · @endif
                @if($offre->lieu) {{ $offre->lieu }} · @endif
                {{ ucfirst($offre->type_contrat) }}
            </div>
            <a href="{{ $offre->url_source }}" style="color:#2563eb;">Voir l'annonce originale →</a>
        </div>
    @endforeach

    <p style="color:#9ca3af; font-size:12px; margin-top:24px;">
        Vous recevez cet e-mail car vous avez une recherche sauvegardée active sur WoutJob.
        Gérez vos alertes depuis l'application.
    </p>
</body>
</html>
