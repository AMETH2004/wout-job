import { Injectable, inject } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable } from 'rxjs';
import { environment } from '../../environments/environment';
import { RechercheSauvegardee } from '../core/models';
import { EcritureReponse } from '../core/api';

export interface RechercheSauvegardeePayload {
  nom: string;
  mots_cles?: string | null;
  lieu?: string | null;
  type_contrat?: string | null;
  categorie?: string | null;
  frequence_alerte?: string;
}

@Injectable({ providedIn: 'root' })
export class RechercheSauvegardeeService {
  private http = inject(HttpClient);
  private url = `${environment.apiUrl}/recherches-sauvegardees`;

  liste(): Observable<RechercheSauvegardee[]> {
    return this.http.get<RechercheSauvegardee[]>(this.url);
  }

  creer(data: RechercheSauvegardeePayload): Observable<EcritureReponse<RechercheSauvegardee>> {
    return this.http.post<EcritureReponse<RechercheSauvegardee>>(this.url, data);
  }

  modifier(id: number, data: Partial<RechercheSauvegardeePayload>): Observable<EcritureReponse<RechercheSauvegardee>> {
    return this.http.put<EcritureReponse<RechercheSauvegardee>>(`${this.url}/${id}`, data);
  }

  supprimer(id: number): Observable<{ message: string }> {
    return this.http.delete<{ message: string }>(`${this.url}/${id}`);
  }
}
