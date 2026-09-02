import { HttpClient, HttpParams } from '@angular/common/http';
import { Injectable, inject } from '@angular/core';
import { Observable } from 'rxjs';
import { environment } from '../../../environments/environment';
import {
  PaginatedResponse,
  Profile,
  ProfilePayload,
} from '../../shared/models/profile.model';

@Injectable({ providedIn: 'root' })
export class ProfilesService {
  private readonly http = inject(HttpClient);
  private readonly baseUrl = `${environment.apiUrl}/profiles`;

  list(page = 1, perPage = 10): Observable<PaginatedResponse<Profile>> {
    const params = new HttpParams()
      .set('page', String(page))
      .set('per_page', String(perPage));

    return this.http.get<PaginatedResponse<Profile>>(this.baseUrl, { params });
  }

  create(payload: ProfilePayload): Observable<Profile> {
    return this.http.post<Profile>(this.baseUrl, payload);
  }

  update(id: string, payload: ProfilePayload): Observable<Profile> {
    return this.http.put<Profile>(`${this.baseUrl}/${id}`, payload);
  }

  delete(id: string): Observable<{ message: string }> {
    return this.http.delete<{ message: string }>(`${this.baseUrl}/${id}`);
  }

  export(format: 'pdf' | 'xlsx'): Observable<Blob> {
    return this.http.get(`${environment.apiUrl}/profiles/export/${format}`, {
      responseType: 'blob',
    });
  }
}
