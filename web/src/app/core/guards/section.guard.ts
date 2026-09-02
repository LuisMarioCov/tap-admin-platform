import { inject } from '@angular/core';
import { CanActivateFn, Router } from '@angular/router';
import { map } from 'rxjs';
import { AuthService } from '../auth/auth.service';
import { SectionKey } from '../models/user.model';

export const sectionGuard = (section: SectionKey): CanActivateFn => {
  return () => {
    const authService = inject(AuthService);
    const router = inject(Router);

    return authService.ensureSession().pipe(
      map((isValid) => {
        if (!isValid) {
          return router.createUrlTree(['/login']);
        }

        return authService.hasSection(section)
          ? true
          : router.createUrlTree(['/prohibido']);
      }),
    );
  };
};
