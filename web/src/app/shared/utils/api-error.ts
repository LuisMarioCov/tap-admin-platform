import { HttpErrorResponse } from '@angular/common/http';

/** First backend validation message, or a concrete fallback (never a generic silent fail). */
export function firstApiError(error: HttpErrorResponse, fallback: string): string {
  const payload = error.error as { errors?: Record<string, string[]>; message?: string } | null;

  if (error.status === 422 && payload) {
    const firstError = Object.values(payload.errors ?? {})[0]?.[0];
    if (firstError) {
      return firstError;
    }
    if (payload.message) {
      return payload.message;
    }
  }

  if (error.status === 429) {
    return 'Demasiadas solicitudes. Espera un momento e inténtalo de nuevo.';
  }

  if (error.status === 413) {
    return 'La foto excede 2 MB, archivo demasiado grande.';
  }

  if (error.status === 0) {
    return 'No se pudo contactar al servidor. Revisa tu conexión o que la API esté encendida.';
  }

  return fallback;
}
