import { Component, OnInit, WritableSignal, inject, signal } from '@angular/core';
import { RouterLink } from '@angular/router';
import { OffreService } from '../../services/offre.service';
import { FavoriService } from '../../services/favori.service';
import { AuthService } from '../../core/auth.service';
import { NotificationService } from '../../core/notification.service';
import { Offre, ResultatRecherche } from '../../core/models';
import { TypeContratBadgeComponent } from '../../shared/type-contrat-badge';

@Component({
  selector: 'app-recherche',
  imports: [RouterLink, TypeContratBadgeComponent],
  templateUrl: './recherche.html',
})
export class RechercheComponent implements OnInit {
  private offreService = inject(OffreService);
  private favoriService = inject(FavoriService);
  private notif = inject(NotificationService);
  auth = inject(AuthService);

  readonly resultat = signal<ResultatRecherche | null>(null);
  readonly chargement = signal(true);

  readonly q = signal('');
  readonly lieu = signal<string | null>(null);
  readonly typeContrat = signal<string | null>(null);
  readonly categorie = signal<string | null>(null);
  readonly tri = signal<'pertinence' | 'recent'>('recent');
  readonly page = signal(1);

  ngOnInit(): void {
    this.charger();
  }

  charger(): void {
    this.chargement.set(true);
    this.offreService
      .rechercher({
        q: this.q(),
        lieu: this.lieu() ?? undefined,
        type_contrat: this.typeContrat() ?? undefined,
        categorie: this.categorie() ?? undefined,
        tri: this.tri(),
        page: this.page(),
      })
      .subscribe({
        next: (r) => {
          this.resultat.set(r);
          this.chargement.set(false);
        },
        error: () => this.chargement.set(false),
      });
  }

  soumettreRecherche(): void {
    this.page.set(1);
    this.charger();
  }

  basculerFiltre(signalRef: WritableSignal<string | null>, valeur: string): void {
    signalRef.set(signalRef() === valeur ? null : valeur);
    this.page.set(1);
    this.charger();
  }

  changerTri(t: 'pertinence' | 'recent'): void {
    this.tri.set(t);
    this.charger();
  }

  pageSuivante(): void {
    const r = this.resultat();
    if (r && this.page() * r.par_page < r.total) {
      this.page.update((p) => p + 1);
      this.charger();
    }
  }

  pagePrecedente(): void {
    if (this.page() > 1) {
      this.page.update((p) => p - 1);
      this.charger();
    }
  }

  totalPages(r: ResultatRecherche): number {
    return Math.max(1, Math.ceil(r.total / r.par_page));
  }

  ajouterFavori(offre: Offre): void {
    if (!this.auth.estConnecte()) {
      this.notif.info('Connectez-vous pour ajouter des favoris.');
      return;
    }
    this.favoriService.ajouter(offre.id).subscribe({
      next: () => this.notif.succes('Ajouté aux favoris.'),
    });
  }

  entriesDe(obj: Record<string, number> | undefined): [string, number][] {
    return obj ? Object.entries(obj).sort((a, b) => b[1] - a[1]) : [];
  }
}
