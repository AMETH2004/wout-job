import { Component, OnInit, inject, signal } from '@angular/core';
import { DatePipe } from '@angular/common';
import { ActivatedRoute, Router, RouterLink } from '@angular/router';
import { OffreService } from '../../services/offre.service';
import { FavoriService } from '../../services/favori.service';
import { AuthService } from '../../core/auth.service';
import { NotificationService } from '../../core/notification.service';
import { Offre } from '../../core/models';
import { TypeContratBadgeComponent } from '../../shared/type-contrat-badge';

@Component({
  selector: 'app-offre-detail',
  imports: [RouterLink, DatePipe, TypeContratBadgeComponent],
  templateUrl: './offre-detail.html',
})
export class OffreDetailComponent implements OnInit {
  private offreService = inject(OffreService);
  private favoriService = inject(FavoriService);
  private notif = inject(NotificationService);
  private route = inject(ActivatedRoute);
  private router = inject(Router);
  auth = inject(AuthService);

  readonly offre = signal<Offre | null>(null);
  readonly chargement = signal(true);

  ngOnInit(): void {
    const id = Number(this.route.snapshot.paramMap.get('id'));
    this.offreService.un(id).subscribe({
      next: (o) => {
        this.offre.set(o);
        this.chargement.set(false);
      },
      error: () => {
        this.chargement.set(false);
        this.router.navigate(['/recherche']);
      },
    });
  }

  ajouterFavori(): void {
    const o = this.offre();
    if (!o) return;
    if (!this.auth.estConnecte()) {
      this.notif.info('Connectez-vous pour ajouter des favoris.');
      return;
    }
    this.favoriService.ajouter(o.id).subscribe({
      next: () => this.notif.succes('Ajouté aux favoris.'),
    });
  }
}
