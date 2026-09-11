import { Injectable, inject } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable } from 'rxjs';
import { environment } from '../../environments/environment';
import { Offre, ResultatRecherche } from '../core/models';
import { toParams } from '../core/api';

export interface FiltresOffres {
  q?: string;
  lieu?: string;
  type_contrat?: string;
  categorie?: string;
  source?: string;
  tri?: 'pertinence' | 'recent';
  page?: number;
  par_page?: number;
}

@Injectable({ providedIn: 'root' })
export class OffreService {
  private http = inject(HttpClient);
  private url = `${environment.apiUrl}/offres`;

  rechercher(filtres: FiltresOffres = {}): Observable<ResultatRecherche> {
    return this.http.get<ResultatRecherche>(this.url, { params: toParams(filtres) });
  }

  un(id: number): Observable<Offre> {
    return this.http.get<Offre>(`${this.url}/${id}`);
  }
}
