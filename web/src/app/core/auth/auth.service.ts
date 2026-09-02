import { HttpClient } from '@angular/common/http';
import { Injectable, computed, inject, signal } from '@angular/core';
import { Router } from '@angular/router';
import { Observable, catchError, map, of, tap, throwError } from 'rxjs';
import { environment } from '../../../environments/environment';
import { LoginResponse, MessageResponse, User } from '../models/user.model';

const TOKEN_STORAGE_KEY = 'tap_auth_token';

@Injectable({ providedIn: 'root' })
export class AuthService {
  private readonly http = inject(HttpClient);
  private readonly router = inject(Router);

  private readonly userState = signal<User | null>(null);
  readonly user = this.userState.asReadonly();
  readonly isAuthenticated = computed(() => this.getToken() !== null);
  readonly allowedSections = computed(() => this.userState()?.allowed_sections ?? []);

  login(email: string, password: string): Observable<User> {
    return this.http
      .post<LoginResponse>(`${environment.apiUrl}/auth/login`, { email, password })
      .pipe(
        tap((response) => this.persistSession(response.token, response.user)),
        map((response) => response.user),
      );
  }

  logout(): Observable<void> {
    if (!this.getToken()) {
      this.clearSession();
      return of(undefined);
    }

    return this.http.post<MessageResponse>(`${environment.apiUrl}/auth/logout`, {}).pipe(
      tap(() => this.clearSession()),
      map(() => undefined),
      catchError((error) => {
        this.clearSession();
        return throwError(() => error);
      }),
    );
  }

  loadMe(): Observable<User> {
    return this.http.get<{ data: User } | User>(`${environment.apiUrl}/auth/me`).pipe(
      map((response) => this.normalizeUser(response)),
      tap((user) => this.userState.set(user)),
    );
  }

  ensureSession(): Observable<boolean> {
    if (!this.getToken()) {
      return of(false);
    }

    if (this.userState()) {
      return of(true);
    }

    return this.loadMe().pipe(
      map((user) => Boolean(user.id && user.email)),
      catchError(() => {
        this.clearSession();
        return of(false);
      }),
    );
  }

  hasSection(section: string): boolean {
    return this.allowedSections().includes(section as User['allowed_sections'][number]);
  }

  getToken(): string | null {
    return localStorage.getItem(TOKEN_STORAGE_KEY);
  }

  redirectAfterLogin(): void {
    void this.router.navigate(['/inicio']);
  }

  private persistSession(token: string, user: User): void {
    localStorage.setItem(TOKEN_STORAGE_KEY, token);
    this.userState.set(user);
  }

  private clearSession(): void {
    localStorage.removeItem(TOKEN_STORAGE_KEY);
    this.userState.set(null);
  }

  private normalizeUser(response: { data: User } | User): User {
    if (typeof response === 'object' && response !== null && 'data' in response && response.data) {
      return response.data;
    }

    return response as User;
  }
}
