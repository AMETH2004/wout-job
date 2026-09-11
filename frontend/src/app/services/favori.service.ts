import { Injectable, inject } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable } from 'rxjs';
import { environment } from '../../environments/environment';
import { Favori } from '../core/models';

@Injectable({ providedIn: 'root' })
export class FavoriService {
  private http = inject(HttpClient);
  private url = `${environment.apiUrl}/favoris`;

  liste(): Observable<Favori[]> {
    return this.http.get<Favori[]>(this.url);
  }

  ajouter(offreId: number): Observable<{ message: string; data: Favori }> {
    return this.http.post<{ message: string; data: Favori }>(this.url, { offre_id: offreId });
  }

  retirer(offreId: number): Observable<{ message: string }> {
    return this.http.delete<{ message: string }>(`${this.url}/${offreId}`);
  }
}
