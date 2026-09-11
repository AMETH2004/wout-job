import { Component, Input } from '@angular/core';

const COULEURS: Record<string, string> = {
  emploi: 'text-bg-primary',
  stage: 'text-bg-success',
  alternance: 'text-bg-info',
  autre: 'text-bg-secondary',
};

@Component({
  selector: 'app-type-contrat-badge',
  template: `<span class="badge text-capitalize" [class]="couleur">{{ type }}</span>`,
})
export class TypeContratBadgeComponent {
  @Input({ required: true }) type!: string;

  get couleur(): string {
    return COULEURS[this.type] ?? 'text-bg-secondary';
  }
}
