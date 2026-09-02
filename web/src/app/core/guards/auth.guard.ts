import { inject } from '@angular/core';
import { CanActivateFn, Router } from '@angular/router';
import { map } from 'rxjs';
import { AuthService } from '../auth/auth.service';

export const authGuard: CanActivateFn = () => {
  const authService = inject(AuthService);
  const router = inject(Router);

  if (!authService.getToken()) {
    return router.createUrlTree(['/login']);
  }

  return authService.ensureSession().pipe(
    map((isValid) => (isValid ? true : router.createUrlTree(['/login']))),
  );
};
