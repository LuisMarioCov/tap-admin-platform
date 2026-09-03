import { HttpClient, HttpParams } from '@angular/common/http';
import { Injectable, inject } from '@angular/core';
import { Observable, map } from 'rxjs';
import { environment } from '../../../environments/environment';
import { User } from '../../core/models/user.model';
import { PaginatedResponse } from '../../shared/models/product.model';

export interface UserFormValues {
  name: string;
  email: string;
  password: string;
  phone: string;
  country_code: string;
  profile_ids: string[];
  photo: File | null;
}

@Injectable({ providedIn: 'root' })
export class UsersService {
  private readonly http = inject(HttpClient);
  private readonly baseUrl = `${environment.apiUrl}/users`;

  list(page = 1, perPage = 10): Observable<PaginatedResponse<User>> {
    const params = new HttpParams()
      .set('page', String(page))
      .set('per_page', String(perPage));

    return this.http.get<PaginatedResponse<User>>(this.baseUrl, { params });
  }

  create(values: UserFormValues): Observable<User> {
    return this.http.post<User>(this.baseUrl, this.toFormData(values, false));
  }

  update(id: string, values: UserFormValues): Observable<User> {
    const formData = this.toFormData(values, true);
    formData.append('_method', 'PUT');

    return this.http.post<User>(`${this.baseUrl}/${id}`, formData);
  }

  delete(id: string): Observable<{ message: string }> {
    return this.http.delete<{ message: string }>(`${this.baseUrl}/${id}`);
  }

  export(format: 'pdf' | 'xlsx'): Observable<Blob> {
    return this.http.get(`${environment.apiUrl}/users/export/${format}`, {
      responseType: 'blob',
    });
  }

  photoObjectUrl(userId: string): Observable<string> {
    return this.http
      .get(`${this.baseUrl}/${userId}/photo`, { responseType: 'blob' })
      .pipe(map((blob) => URL.createObjectURL(blob)));
  }

  private toFormData(values: UserFormValues, isUpdate: boolean): FormData {
    const formData = new FormData();

    formData.append('name', values.name);
    formData.append('email', values.email);
    formData.append('phone', values.phone ?? '');
    formData.append('country_code', values.country_code ?? '');

    if (values.password) {
      formData.append('password', values.password);
    } else if (!isUpdate) {
      formData.append('password', '');
    }

    values.profile_ids.forEach((id, index) => {
      formData.append(`profile_ids[${index}]`, id);
    });

    if (values.photo) {
      formData.append('photo', values.photo);
    }

    return formData;
  }
}
