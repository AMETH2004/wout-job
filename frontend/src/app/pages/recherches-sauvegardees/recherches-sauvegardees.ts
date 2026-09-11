import { Component, OnInit, inject, signal } from '@angular/core';
import { DatePipe } from '@angular/common';
import { FormBuilder, ReactiveFormsModule, Validators } from '@angular/forms';
import { RechercheSauvegardeeService } from '../../services/recherche-sauvegardee.service';
import { NotificationService } from '../../core/notification.service';
import { RechercheSauvegardee } from '../../core/models';
import { ModalComponent } from '../../shared/modal';

@Component({
  selector: 'app-recherches-sauvegardees',
  imports: [ReactiveFormsModule, DatePipe, ModalComponent],
  templateUrl: './recherches-sauvegardees.html',
})
export class RecherchesSauvegardeesComponent implements OnInit {
  private service = inject(RechercheSauvegardeeService);
  private fb = inject(FormBuilder);
  private notif = inject(NotificationService);

  readonly recherches = signal<RechercheSauvegardee[]>([]);
  readonly chargement = signal(true);
  readonly modalOuvert = signal(false);
  readonly enregistrement = signal(false);
  readonly editionId = signal<number | null>(null);

  readonly form = this.fb.nonNullable.group({
    nom: ['', Validators.required],
    mots_cles: [''],
    lieu: [''],
    type_contrat: [''],
    categorie: [''],
    frequence_alerte: ['quotidienne'],
  });

  ngOnInit(): void {
    this.charger();
  }

  charger(): void {
    this.chargement.set(true);
    this.service.liste().subscribe({
      next: (r) => {
        this.recherches.set(r);
        this.chargement.set(false);
      },
      error: () => this.chargement.set(false),
    });
  }

  ouvrirCreation(): void {
    this.editionId.set(null);
    this.form.reset({ frequence_alerte: 'quotidienne' });
    this.modalOuvert.set(true);
  }

  ouvrirEdition(r: RechercheSauvegardee): void {
    this.editionId.set(r.id);
    this.form.reset({
      nom: r.nom,
      mots_cles: r.mots_cles ?? '',
      lieu: r.lieu ?? '',
      type_contrat: r.type_contrat ?? '',
      categorie: r.categorie ?? '',
      frequence_alerte: r.frequence_alerte,
    });
    this.modalOuvert.set(true);
  }

  enregistrer(): void {
    if (this.form.invalid) {
      this.form.markAllAsTouched();
      return;
    }
    this.enregistrement.set(true);
    const v = this.form.getRawValue();
    const data = {
      nom: v.nom,
      mots_cles: v.mots_cles || null,
      lieu: v.lieu || null,
      type_contrat: v.type_contrat || null,
      categorie: v.categorie || null,
      frequence_alerte: v.frequence_alerte,
    };
    const id = this.editionId();
    const req = id ? this.service.modifier(id, data) : this.service.creer(data);

    req.subscribe({
      next: (res) => {
        this.notif.succes(res.message);
        this.modalOuvert.set(false);
        this.enregistrement.set(false);
        this.charger();
      },
      error: () => this.enregistrement.set(false),
    });
  }

  supprimer(r: RechercheSauvegardee): void {
    if (!confirm(`Supprimer la recherche « ${r.nom} » ?`)) return;
    this.service.supprimer(r.id).subscribe({
      next: (res) => {
        this.notif.succes(res.message);
        this.charger();
      },
    });
  }
}
