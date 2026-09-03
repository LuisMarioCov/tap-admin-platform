import { CommonModule } from '@angular/common';
import { HttpErrorResponse } from '@angular/common/http';
import { Component, inject, signal } from '@angular/core';
import { FormBuilder, ReactiveFormsModule, Validators } from '@angular/forms';
import { SECTION_OPTIONS } from '../../shared/config/section-options';
import { SectionKey } from '../../core/models/user.model';
import { Profile } from '../../shared/models/profile.model';
import { downloadBlob } from '../../shared/utils/download-blob';
import { firstApiError } from '../../shared/utils/api-error';
import { ProfilesService } from './profiles.service';

@Component({
  selector: 'app-profiles-page',
  standalone: true,
  imports: [CommonModule, ReactiveFormsModule],
  templateUrl: './profiles-page.component.html',
  styleUrl: './profiles-page.component.scss',
})
export class ProfilesPageComponent {
  private readonly profilesService = inject(ProfilesService);
  private readonly formBuilder = inject(FormBuilder);

  readonly sectionOptions = SECTION_OPTIONS;
  readonly profiles = signal<Profile[]>([]);
  readonly loading = signal(false);
  readonly saving = signal(false);
  readonly exporting = signal<'pdf' | 'xlsx' | null>(null);
  readonly errorMessage = signal<string | null>(null);
  readonly successMessage = signal<string | null>(null);
  readonly editingProfile = signal<Profile | null>(null);
  readonly currentPage = signal(1);
  readonly lastPage = signal(1);
  readonly total = signal(0);

  readonly form = this.formBuilder.nonNullable.group({
    name: ['', [Validators.required, Validators.maxLength(120)]],
    products: [false],
    users: [false],
    profiles: [false],
  });

  constructor() {
    this.loadProfiles();
  }

  loadProfiles(page = this.currentPage()): void {
    this.loading.set(true);
    this.errorMessage.set(null);

    this.profilesService.list(page).subscribe({
      next: (response) => {
        this.profiles.set(response.data);
        this.currentPage.set(response.meta.current_page);
        this.lastPage.set(response.meta.last_page);
        this.total.set(response.meta.total);
        this.loading.set(false);
      },
      error: () => {
        this.loading.set(false);
        this.errorMessage.set('No pudimos cargar los perfiles.');
      },
    });
  }

  startCreate(): void {
    this.editingProfile.set(null);
    this.form.reset({ name: '', products: false, users: false, profiles: false });
    this.clearMessages();
  }

  startEdit(profile: Profile): void {
    this.editingProfile.set(profile);
    this.form.reset({
      name: profile.name,
      products: profile.section_keys.includes('products'),
      users: profile.section_keys.includes('users'),
      profiles: profile.section_keys.includes('profiles'),
    });
    this.clearMessages();
  }

  submit(): void {
    this.clearMessages();

    if (this.form.invalid) {
      this.form.markAllAsTouched();
      return;
    }

    const sectionKeys = this.buildSectionKeys();
    if (sectionKeys.length === 0) {
      this.errorMessage.set('Selecciona al menos una sección.');
      return;
    }

    const payload = {
      name: this.form.controls.name.value,
      section_keys: sectionKeys,
    };

    const editing = this.editingProfile();
    this.saving.set(true);

    const request$ = editing
      ? this.profilesService.update(editing.id, payload)
      : this.profilesService.create(payload);

    request$.subscribe({
      next: () => {
        this.saving.set(false);
        this.successMessage.set(
          editing ? 'Perfil actualizado correctamente.' : 'Perfil creado correctamente.',
        );
        this.startCreate();
        this.loadProfiles(editing ? this.currentPage() : 1);
      },
      error: (error: HttpErrorResponse) => {
        this.saving.set(false);
        this.errorMessage.set(this.resolveErrorMessage(error));
      },
    });
  }

  remove(profile: Profile): void {
    const confirmed = window.confirm(`¿Eliminar el perfil ${profile.code}?`);

    if (!confirmed) {
      return;
    }

    this.clearMessages();
    this.profilesService.delete(profile.id).subscribe({
      next: () => {
        this.successMessage.set('Perfil eliminado.');
        this.loadProfiles(this.currentPage());
      },
      error: (error: HttpErrorResponse) => {
        this.errorMessage.set(this.resolveErrorMessage(error));
      },
    });
  }

  export(format: 'pdf' | 'xlsx'): void {
    this.clearMessages();
    this.exporting.set(format);

    this.profilesService.export(format).subscribe({
      next: (blob) => {
        downloadBlob(blob, `perfiles.${format}`);
        this.exporting.set(null);
        this.successMessage.set(`Exportación ${format.toUpperCase()} descargada.`);
      },
      error: () => {
        this.exporting.set(null);
        this.errorMessage.set('No pudimos exportar los perfiles.');
      },
    });
  }

  goToPage(page: number): void {
    if (page < 1 || page > this.lastPage() || page === this.currentPage()) {
      return;
    }

    this.loadProfiles(page);
  }

  formatSections(keys: SectionKey[]): string {
    return keys.join(', ');
  }

  private buildSectionKeys(): SectionKey[] {
    const keys: SectionKey[] = [];
    const value = this.form.getRawValue();

    if (value.products) keys.push('products');
    if (value.users) keys.push('users');
    if (value.profiles) keys.push('profiles');

    return keys;
  }

  private clearMessages(): void {
    this.errorMessage.set(null);
    this.successMessage.set(null);
  }

  private resolveErrorMessage(error: HttpErrorResponse): string {
    return firstApiError(error, 'Ocurrió un error al guardar el perfil.');
  }
}
