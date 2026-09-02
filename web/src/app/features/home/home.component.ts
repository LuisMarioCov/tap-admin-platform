import { CommonModule } from '@angular/common';
import { Component, computed, inject } from '@angular/core';
import { RouterLink } from '@angular/router';
import { AuthService } from '../../core/auth/auth.service';
import { NAV_ITEMS } from '../../shared/config/navigation.config';

@Component({
  selector: 'app-home',
  standalone: true,
  imports: [CommonModule, RouterLink],
  templateUrl: './home.component.html',
  styleUrl: './home.component.scss',
})
export class HomeComponent {
  private readonly authService = inject(AuthService);

  readonly user = this.authService.user;
  readonly shortcuts = computed(() => {
    const allowed = new Set(this.authService.allowedSections());

    return NAV_ITEMS.filter((item) => allowed.has(item.section));
  });
}
