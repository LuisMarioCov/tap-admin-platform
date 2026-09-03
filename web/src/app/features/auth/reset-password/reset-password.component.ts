import { CommonModule } from '@angular/common';
import { HttpErrorResponse } from '@angular/common/http';
import { Component, inject, signal } from '@angular/core';
import { FormBuilder, ReactiveFormsModule, Validators } from '@angular/forms';
import { ActivatedRoute, Router, RouterLink } from '@angular/router';
import { AuthService } from '../../../core/auth/auth.service';

@Component({
  selector: 'app-reset-password',
  standalone: true,
  imports: [CommonModule, ReactiveFormsModule, RouterLink],
  templateUrl: './reset-password.component.html',
  styleUrl: './reset-password.component.scss',
})
export class ResetPasswordComponent {
  private readonly authService = inject(AuthService);
  private readonly formBuilder = inject(FormBuilder);
  private readonly route = inject(ActivatedRoute);
  private readonly router = inject(Router);

  readonly loading = signal(false);
  readonly errorMessage = signal<string | null>(null);
  readonly successMessage = signal<string | null>(null);

  readonly form = this.formBuilder.nonNullable.group({
    email: ['', [Validators.required, Validators.email]],
    token: ['', [Validators.required]],
    password: ['', [Validators.required, Validators.minLength(8)]],
    password_confirmation: ['', [Validators.required]],
  });

  constructor() {
    const params = this.route.snapshot.queryParamMap;
    this.form.patchValue({
      email: params.get('email') ?? '',
      token: params.get('token') ?? '',
    });
  }

  submit(): void {
    this.errorMessage.set(null);
    this.successMessage.set(null);

    if (this.form.invalid) {
      this.form.markAllAsTouched();
      return;
    }

    const { email, token, password, password_confirmation } = this.form.getRawValue();
    if (password !== password_confirmation) {
      this.errorMessage.set('Las contraseñas no coinciden.');
      return;
    }

    this.loading.set(true);
    this.authService.resetPassword(email, token, password, password_confirmation).subscribe({
      next: (response) => {
        this.loading.set(false);
        this.successMessage.set(response.message);
        setTimeout(() => void this.router.navigate(['/login']), 1500);
      },
      error: (error: HttpErrorResponse) => {
        this.loading.set(false);
        const payload = error.error as { errors?: Record<string, string[]>; message?: string };
        const firstError = Object.values(payload.errors ?? {})[0]?.[0];
        this.errorMessage.set(firstError ?? payload.message ?? 'No pudimos actualizar la contraseña.');
      },
    });
  }
}
