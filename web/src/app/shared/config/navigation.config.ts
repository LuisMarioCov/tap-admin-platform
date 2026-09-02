import { SectionKey } from '../../core/models/user.model';

export interface NavItem {
  section: SectionKey;
  label: string;
  route: string;
  description: string;
}

export const NAV_ITEMS: NavItem[] = [
  {
    section: 'products',
    label: 'Productos',
    route: '/productos',
    description: 'Catálogo, precios y exportaciones',
  },
  {
    section: 'users',
    label: 'Usuarios',
    route: '/usuarios',
    description: 'Cuentas, fotos y perfiles asignados',
  },
  {
    section: 'profiles',
    label: 'Perfiles',
    route: '/perfiles',
    description: 'Roles y secciones permitidas',
  },
];
