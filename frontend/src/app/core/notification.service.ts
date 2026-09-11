import { Injectable, signal } from '@angular/core';

export interface Notification {
  id: number;
  type: 'succes' | 'erreur' | 'info';
  message: string;
}

@Injectable({ providedIn: 'root' })
export class NotificationService {
  readonly liste = signal<Notification[]>([]);
  private compteur = 0;

  succes(message: string): void {
    this.ajouter('succes', message);
  }

  erreur(message: string): void {
    this.ajouter('erreur', message);
  }

  info(message: string): void {
    this.ajouter('info', message);
  }

  fermer(id: number): void {
    this.liste.update((l) => l.filter((n) => n.id !== id));
  }

  private ajouter(type: Notification['type'], message: string): void {
    const id = ++this.compteur;
    this.liste.update((l) => [...l, { id, type, message }]);
    setTimeout(() => this.fermer(id), 5000);
  }
}
