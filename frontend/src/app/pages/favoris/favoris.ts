import { Component, OnInit, inject, signal } from '@angular/core';
import { RouterLink } from '@angular/router';
import { FavoriService } from '../../services/favori.service';
import { NotificationService } from '../../core/notification.service';
import { Favori } from '../../core/models';
import { TypeContratBadgeComponent } from '../../shared/type-contrat-badge';

@Component({
  selector: 'app-favoris',
  imports: [RouterLink, TypeContratBadgeComponent],
  templateUrl: './favoris.html',
})
export class FavorisComponent implements OnInit {
  private service = inject(FavoriService);
  private notif = inject(NotificationService);

  readonly favoris = signal<Favori[]>([]);
  readonly chargement = signal(true);

  ngOnInit(): void {
    this.charger();
  }

  charger(): void {
    this.chargement.set(true);
    this.service.liste().subscribe({
      next: (f) => {
        this.favoris.set(f);
        this.chargement.set(false);
      },
      error: () => this.chargement.set(false),
    });
  }

  retirer(favori: Favori): void {
    this.service.retirer(favori.offre_id).subscribe({
      next: (res) => {
        this.notif.succes(res.message);
        this.charger();
      },
    });
  }
}
