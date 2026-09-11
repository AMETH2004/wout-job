import { Injectable, inject } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable } from 'rxjs';
import { environment } from '../../environments/environment';
import { Source } from '../core/models';

@Injectable({ providedIn: 'root' })
export class SourceService {
  private http = inject(HttpClient);

  liste(): Observable<Source[]> {
    return this.http.get<Source[]>(`${environment.apiUrl}/sources`);
  }
}
