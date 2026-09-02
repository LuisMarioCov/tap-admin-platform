import { Routes } from '@angular/router';
import { authGuard } from './core/guards/auth.guard';
import { guestGuard } from './core/guards/guest.guard';
import { sectionGuard } from './core/guards/section.guard';

export const routes: Routes = [
  {
    path: 'login',
    canActivate: [guestGuard],
    loadComponent: () =>
      import('./features/auth/login/login.component').then((m) => m.LoginComponent),
  },
  {
    path: 'prohibido',
    loadComponent: () =>
      import('./features/errors/forbidden/forbidden.component').then(
        (m) => m.ForbiddenComponent,
      ),
  },
  {
    path: '',
    canActivate: [authGuard],
    loadComponent: () => import('./layout/shell/shell.component').then((m) => m.ShellComponent),
    children: [
      {
        path: 'inicio',
        loadComponent: () => import('./features/home/home.component').then((m) => m.HomeComponent),
      },
      {
        path: 'productos',
        canActivate: [sectionGuard('products')],
        loadComponent: () =>
          import('./features/products/products-page.component').then(
            (m) => m.ProductsPageComponent,
          ),
      },
      {
        path: 'usuarios',
        canActivate: [sectionGuard('users')],
        loadComponent: () =>
          import('./features/users/users-page.component').then((m) => m.UsersPageComponent),
      },
      {
        path: 'perfiles',
        canActivate: [sectionGuard('profiles')],
        loadComponent: () =>
          import('./features/profiles/profiles-page.component').then(
            (m) => m.ProfilesPageComponent,
          ),
      },
      { path: '', pathMatch: 'full', redirectTo: 'inicio' },
    ],
  },
  { path: '**', redirectTo: 'inicio' },
];
