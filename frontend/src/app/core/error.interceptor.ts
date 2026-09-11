import { HttpErrorResponse, HttpInterceptorFn } from '@angular/common/http';
import { inject } from '@angular/core';
import { Router } from '@angular/router';
import { catchError, throwError } from 'rxjs';
import { NotificationService } from './notification.service';

export const errorInterceptor: HttpInterceptorFn = (req, next) => {
  const router = inject(Router);
  const notif = inject(NotificationService);

  return next(req).pipe(
    catchError((err: HttpErrorResponse) => {
      if (err.status === 401) {
        localStorage.clear();
        router.navigate(['/connexion']);
      } else if (err.status === 403) {
        notif.erreur("Vous n'avez pas les droits pour cette action.");
      } else if (err.status === 422) {
        const premier = err.error?.errors ? Object.values(err.error.errors)[0] : null;
        notif.erreur(
          (Array.isArray(premier) ? premier[0] : premier) ?? err.error?.message ?? 'Données invalides.',
        );
      } else if (err.status >= 500) {
        notif.erreur('Erreur serveur. Réessayez plus tard.');
      } else if (err.status === 0) {
        notif.erreur("Serveur injoignable. Vérifiez que l'API est démarrée.");
      }
      return throwError(() => err);
    }),
  );
};
