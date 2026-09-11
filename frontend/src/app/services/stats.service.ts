import { Injectable, inject } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable } from 'rxjs';
import { environment } from '../../environments/environment';
import { Stats } from '../core/models';

@Injectable({ providedIn: 'root' })
export class StatsService {
  private http = inject(HttpClient);

  charger(): Observable<Stats> {
    return this.http.get<Stats>(`${environment.apiUrl}/stats`);
  }
}
