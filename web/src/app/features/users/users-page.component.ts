import { CommonModule } from '@angular/common';
import { HttpErrorResponse } from '@angular/common/http';
import { Component, OnDestroy, inject, signal } from '@angular/core';
import { FormBuilder, ReactiveFormsModule, Validators } from '@angular/forms';
import { User } from '../../core/models/user.model';
import { Profile } from '../../shared/models/profile.model';
import { downloadBlob } from '../../shared/utils/download-blob';
import { firstApiError } from '../../shared/utils/api-error';
import { ProfilesService } from '../profiles/profiles.service';
import { UsersService } from './users.service';

@Component({
  selector: 'app-users-page',
  standalone: true,
  imports: [CommonModule, ReactiveFormsModule],
  templateUrl: './users-page.component.html',
  styleUrl: './users-page.component.scss',
})
export class UsersPageComponent implements OnDestroy {
  private readonly usersService = inject(UsersService);
  private readonly profilesService = inject(ProfilesService);
  private readonly formBuilder = inject(FormBuilder);

  readonly users = signal<User[]>([]);
  readonly profiles = signal<Profile[]>([]);
  readonly photoPreviews = signal<Record<string, string>>({});
  readonly loading = signal(false);
  readonly saving = signal(false);
  readonly exporting = signal<'pdf' | 'xlsx' | null>(null);
  readonly errorMessage = signal<string | null>(null);
  readonly successMessage = signal<string | null>(null);
  readonly editingUser = signal<User | null>(null);
  readonly selectedPhotoName = signal<string | null>(null);
  readonly currentPage = signal(1);
  readonly lastPage = signal(1);
  readonly total = signal(0);

  private selectedPhoto: File | null = null;

  readonly form = this.formBuilder.nonNullable.group({
    name: ['', [Validators.required, Validators.maxLength(120)]],
    email: ['', [Validators.required, Validators.email]],
    password: ['', [Validators.minLength(8)]],
    phone: ['', [Validators.maxLength(20)]],
    country_code: ['+52', [Validators.maxLength(6)]],
    profile_ids: [[] as string[], [Validators.required, Validators.minLength(1)]],
  });

  constructor() {
    this.loadProfiles();
    this.loadUsers();
  }

  ngOnDestroy(): void {
    Object.values(this.photoPreviews()).forEach((url) => URL.revokeObjectURL(url));
  }

  loadProfiles(): void {
    this.profilesService.list(1, 100).subscribe({
      next: (response) => this.profiles.set(response.data),
      error: () => this.errorMessage.set('No pudimos cargar los perfiles.'),
    });
  }

  loadUsers(page = this.currentPage()): void {
    this.loading.set(true);
    this.errorMessage.set(null);

    this.usersService.list(page).subscribe({
      next: (response) => {
        this.users.set(response.data);
        this.currentPage.set(response.meta.current_page);
        this.lastPage.set(response.meta.last_page);
        this.total.set(response.meta.total);
        this.loading.set(false);
        this.loadPhotos(response.data);
      },
      error: () => {
        this.loading.set(false);
        this.errorMessage.set('No pudimos cargar los usuarios.');
      },
    });
  }

  startCreate(): void {
    this.editingUser.set(null);
    this.selectedPhoto = null;
    this.selectedPhotoName.set(null);
    this.form.reset({
      name: '',
      email: '',
      password: '',
      phone: '',
      country_code: '+52',
      profile_ids: [],
    });
    this.form.controls.password.setValidators([Validators.required, Validators.minLength(8)]);
    this.form.controls.password.updateValueAndValidity();
    this.clearMessages();
  }

  startEdit(user: User): void {
    this.editingUser.set(user);
    this.selectedPhoto = null;
    this.selectedPhotoName.set(null);
    this.form.reset({
      name: user.name,
      email: user.email,
      password: '',
      phone: user.phone ?? '',
      country_code: user.country_code ?? '+52',
      profile_ids: [...(user.profile_ids ?? [])],
    });
    this.form.controls.password.setValidators([Validators.minLength(8)]);
    this.form.controls.password.updateValueAndValidity();
    this.clearMessages();
  }

  onPhotoSelected(event: Event): void {
    const input = event.target as HTMLInputElement;
    const file = input.files?.[0] ?? null;

    if (file && file.size > 2 * 1024 * 1024) {
      this.selectedPhoto = null;
      this.selectedPhotoName.set(null);
      input.value = '';
      this.errorMessage.set('La foto excede 2 MB, archivo demasiado grande.');
      return;
    }

    this.selectedPhoto = file;
    this.selectedPhotoName.set(file?.name ?? null);
  }

  toggleProfile(profileId: string, checked: boolean): void {
    const current = [...this.form.controls.profile_ids.value];
    if (checked && !current.includes(profileId)) {
      current.push(profileId);
    }
    if (!checked) {
      const index = current.indexOf(profileId);
      if (index >= 0) {
        current.splice(index, 1);
      }
    }
    this.form.controls.profile_ids.setValue(current);
    this.form.controls.profile_ids.markAsTouched();
  }

  isProfileSelected(profileId: string): boolean {
    return this.form.controls.profile_ids.value.includes(profileId);
  }

  submit(): void {
    this.clearMessages();

    if (this.form.invalid) {
      this.form.markAllAsTouched();
      return;
    }

    const editing = this.editingUser();
    if (!editing && !this.selectedPhoto) {
      this.errorMessage.set('La foto es obligatoria al crear un usuario.');
      return;
    }

    if (this.form.controls.profile_ids.value.length === 0) {
      this.errorMessage.set('Selecciona al menos un perfil.');
      return;
    }

    const raw = this.form.getRawValue();
    const values = {
      name: raw.name,
      email: raw.email,
      password: raw.password,
      phone: raw.phone,
      country_code: raw.country_code,
      profile_ids: raw.profile_ids,
      photo: this.selectedPhoto,
    };

    this.saving.set(true);
    const request$ = editing
      ? this.usersService.update(editing.id, values)
      : this.usersService.create(values);

    request$.subscribe({
      next: () => {
        this.saving.set(false);
        this.successMessage.set(
          editing ? 'Usuario actualizado correctamente.' : 'Usuario creado correctamente.',
        );
        this.startCreate();
        this.loadUsers(editing ? this.currentPage() : 1);
      },
      error: (error: HttpErrorResponse) => {
        this.saving.set(false);
        this.errorMessage.set(this.resolveErrorMessage(error));
      },
    });
  }

  remove(user: User): void {
    if (!window.confirm(`¿Eliminar el usuario ${user.code}?`)) {
      return;
    }

    this.clearMessages();
    this.usersService.delete(user.id).subscribe({
      next: () => {
        this.successMessage.set('Usuario eliminado.');
        this.loadUsers(this.currentPage());
      },
      error: (error: HttpErrorResponse) => {
        this.errorMessage.set(this.resolveErrorMessage(error));
      },
    });
  }

  export(format: 'pdf' | 'xlsx'): void {
    this.clearMessages();
    this.exporting.set(format);

    this.usersService.export(format).subscribe({
      next: (blob) => {
        downloadBlob(blob, `usuarios.${format}`);
        this.exporting.set(null);
        this.successMessage.set(`Exportación ${format.toUpperCase()} descargada.`);
      },
      error: () => {
        this.exporting.set(null);
        this.errorMessage.set('No pudimos exportar los usuarios.');
      },
    });
  }

  goToPage(page: number): void {
    if (page < 1 || page > this.lastPage() || page === this.currentPage()) {
      return;
    }

    this.loadUsers(page);
  }

  profileNames(user: User): string {
    if (user.profiles?.length) {
      return user.profiles.map((profile) => profile.name).join(', ');
    }

    return user.profile_ids.join(', ');
  }

  private loadPhotos(users: User[]): void {
    for (const user of users) {
      if (!user.photo_file_id || this.photoPreviews()[user.id]) {
        continue;
      }

      this.usersService.photoObjectUrl(user.id).subscribe({
        next: (url) => {
          this.photoPreviews.update((current) => ({ ...current, [user.id]: url }));
        },
      });
    }
  }

  private clearMessages(): void {
    this.errorMessage.set(null);
    this.successMessage.set(null);
  }

  private resolveErrorMessage(error: HttpErrorResponse): string {
    if (error.status === 0) {
      return 'La foto excede 2 MB, archivo demasiado grande.';
    }

    return firstApiError(error, 'Ocurrió un error al guardar el usuario.');
  }
}
