import { Component, OnInit, inject, signal } from '@angular/core';
import { DatePipe } from '@angular/common';
import { SourceService } from '../../services/source.service';
import { StatsService } from '../../services/stats.service';
import { Source, Stats } from '../../core/models';

@Component({
  selector: 'app-sources',
  imports: [DatePipe],
  templateUrl: './sources.html',
})
export class SourcesComponent implements OnInit {
  private sourceService = inject(SourceService);
  private statsService = inject(StatsService);

  readonly sources = signal<Source[]>([]);
  readonly stats = signal<Stats | null>(null);
  readonly chargement = signal(true);

  ngOnInit(): void {
    this.sourceService.liste().subscribe((s) => {
      this.sources.set(s);
      this.chargement.set(false);
    });
    this.statsService.charger().subscribe((s) => this.stats.set(s));
  }

  couleurStatut(statut: string): string {
    return statut === 'succes' ? 'text-bg-success' : statut === 'echec' ? 'text-bg-danger' : 'text-bg-secondary';
  }
}
