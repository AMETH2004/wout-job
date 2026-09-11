import { Component, EventEmitter, Input, Output } from '@angular/core';

/** Modale Bootstrap pilotée par `ouvert` (pas de dépendance au JS de Bootstrap). */
@Component({
  selector: 'app-modal',
  template: `
    @if (ouvert) {
      <div class="modal fade show d-block" tabindex="-1" (click)="fondClique()">
        <div
          class="modal-dialog modal-dialog-centered"
          [class.modal-lg]="taille === 'lg'"
          (click)="$event.stopPropagation()"
        >
          <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title">{{ titre }}</h5>
              <button type="button" class="btn-close" (click)="fermer.emit()"></button>
            </div>
            <div class="modal-body">
              <ng-content />
            </div>
            <div class="modal-footer">
              <ng-content select="[pied]" />
            </div>
          </div>
        </div>
      </div>
      <div class="modal-backdrop fade show"></div>
    }
  `,
})
export class ModalComponent {
  @Input() ouvert = false;
  @Input() titre = '';
  @Input() taille: 'md' | 'lg' = 'md';
  @Output() fermer = new EventEmitter<void>();

  fondClique(): void {
    this.fermer.emit();
  }
}
