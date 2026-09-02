import { Component } from '@angular/core';

@Component({
  selector: 'app-users-page',
  standalone: true,
  template: `
    <section class="page">
      <p class="eyebrow">Módulo</p>
      <h1>Usuarios</h1>
      <p>Esta sección está protegida por <code>section:users</code>.</p>
      <p>El operador no ve este menú y tampoco puede entrar por URL directa.</p>
    </section>
  `,
  styles: `
    .page {
      max-width: 720px;
    }

    .eyebrow {
      margin: 0;
      font-size: 0.75rem;
      letter-spacing: 0.12em;
      text-transform: uppercase;
      color: var(--muted);
    }

    h1 {
      margin: 0.35rem 0 0.75rem;
    }

    code {
      font-family: Consolas, monospace;
    }
  `,
})
export class UsersPageComponent {}
