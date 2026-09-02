export interface Product {
  id: string;
  code: string;
  name: string;
  brand: string;
  price: number;
  created_at: string | null;
}

export interface ProductPayload {
  name: string;
  brand: string;
  price: number;
}

export interface PaginationMeta {
  current_page: number;
  last_page: number;
  per_page: number;
  total: number;
}

export interface PaginatedResponse<T> {
  data: T[];
  meta: PaginationMeta;
}
