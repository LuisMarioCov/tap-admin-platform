import { SectionKey } from '../../core/models/user.model';

export interface SectionOption {
  key: SectionKey;
  label: string;
}

export const SECTION_OPTIONS: SectionOption[] = [
  { key: 'products', label: 'Productos' },
  { key: 'users', label: 'Usuarios' },
  { key: 'profiles', label: 'Perfiles' },
];
