export type SectionKey = 'products' | 'users' | 'profiles';

export interface ProfileSummary {
  id: string;
  code: string;
  name: string;
  section_keys: SectionKey[];
}

export interface User {
  id: string;
  code: string;
  name: string;
  email: string;
  phone: string | null;
  country_code: string | null;
  photo_file_id: string | null;
  profile_ids: string[];
  profiles: ProfileSummary[];
  allowed_sections: SectionKey[];
  created_at: string | null;
}

export interface LoginResponse {
  token: string;
  user: User;
}

export interface MessageResponse {
  message: string;
  reset_url?: string;
}
