import { PaginatedResponse } from './product.model';
import { SectionKey } from '../../core/models/user.model';

export interface Profile {
  id: string;
  code: string;
  name: string;
  section_keys: SectionKey[];
  created_at: string | null;
}

export interface ProfilePayload {
  name: string;
  section_keys: SectionKey[];
}

export type { PaginatedResponse };
