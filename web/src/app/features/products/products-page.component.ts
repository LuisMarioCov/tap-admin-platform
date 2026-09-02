import { CommonModule } from '@angular/common';
import { HttpErrorResponse } from '@angular/common/http';
import { Component, inject, signal } from '@angular/core';
import { FormBuilder, ReactiveFormsModule, Validators } from '@angular/forms';
import { Product } from '../../shared/models/product.model';
import { downloadBlob } from '../../shared/utils/download-blob';
import { ProductsService } from './products.service';

@Component({
  selector: 'app-products-page',
  standalone: true,
  imports: [CommonModule, ReactiveFormsModule],
  templateUrl: './products-page.component.html',
  styleUrl: './products-page.component.scss',
})
export class ProductsPageComponent {
  private readonly productsService = inject(ProductsService);
  private readonly formBuilder = inject(FormBuilder);

  readonly products = signal<Product[]>([]);
  readonly loading = signal(false);
  readonly saving = signal(false);
  readonly exporting = signal<'pdf' | 'xlsx' | null>(null);
  readonly errorMessage = signal<string | null>(null);
  readonly successMessage = signal<string | null>(null);
  readonly editingProduct = signal<Product | null>(null);
  readonly currentPage = signal(1);
  readonly lastPage = signal(1);
  readonly total = signal(0);

  readonly form = this.formBuilder.nonNullable.group({
    name: ['', [Validators.required, Validators.maxLength(120)]],
    brand: ['', [Validators.required, Validators.maxLength(120)]],
    price: [
      1,
      [Validators.required, Validators.min(1), Validators.max(999), Validators.pattern(/^\d+$/)],
    ],
  });

  constructor() {
    this.loadProducts();
  }

  loadProducts(page = this.currentPage()): void {
    this.loading.set(true);
    this.errorMessage.set(null);

    this.productsService.list(page).subscribe({
      next: (response) => {
        this.products.set(response.data);
        this.currentPage.set(response.meta.current_page);
        this.lastPage.set(response.meta.last_page);
        this.total.set(response.meta.total);
        this.loading.set(false);
      },
      error: () => {
        this.loading.set(false);
        this.errorMessage.set('No pudimos cargar los productos.');
      },
    });
  }

  startCreate(): void {
    this.editingProduct.set(null);
    this.form.reset({ name: '', brand: '', price: 1 });
    this.clearMessages();
  }

  startEdit(product: Product): void {
    this.editingProduct.set(product);
    this.form.reset({
      name: product.name,
      brand: product.brand,
      price: product.price,
    });
    this.clearMessages();
  }

  submit(): void {
    this.clearMessages();

    if (this.form.invalid) {
      this.form.markAllAsTouched();
      return;
    }

    const payload = this.form.getRawValue();
    const editing = this.editingProduct();
    this.saving.set(true);

    const request$ = editing
      ? this.productsService.update(editing.id, payload)
      : this.productsService.create(payload);

    request$.subscribe({
      next: () => {
        this.saving.set(false);
        this.successMessage.set(
          editing ? 'Producto actualizado correctamente.' : 'Producto creado correctamente.',
        );
        this.startCreate();
        this.loadProducts(editing ? this.currentPage() : 1);
      },
      error: (error: HttpErrorResponse) => {
        this.saving.set(false);
        this.errorMessage.set(this.resolveErrorMessage(error));
      },
    });
  }

  remove(product: Product): void {
    const confirmed = window.confirm(`¿Eliminar el producto ${product.code}?`);

    if (!confirmed) {
      return;
    }

    this.clearMessages();
    this.productsService.delete(product.id).subscribe({
      next: () => {
        this.successMessage.set('Producto eliminado.');
        this.loadProducts(this.currentPage());
      },
      error: () => {
        this.errorMessage.set('No pudimos eliminar el producto.');
      },
    });
  }

  export(format: 'pdf' | 'xlsx'): void {
    this.clearMessages();
    this.exporting.set(format);

    this.productsService.export(format).subscribe({
      next: (blob) => {
        const extension = format === 'pdf' ? 'pdf' : 'xlsx';
        downloadBlob(blob, `productos.${extension}`);
        this.exporting.set(null);
        this.successMessage.set(`Exportación ${format.toUpperCase()} descargada.`);
      },
      error: () => {
        this.exporting.set(null);
        this.errorMessage.set('No pudimos exportar los productos.');
      },
    });
  }

  goToPage(page: number): void {
    if (page < 1 || page > this.lastPage() || page === this.currentPage()) {
      return;
    }

    this.loadProducts(page);
  }

  private clearMessages(): void {
    this.errorMessage.set(null);
    this.successMessage.set(null);
  }

  private resolveErrorMessage(error: HttpErrorResponse): string {
    if (error.status === 422) {
      const payload = error.error as { errors?: Record<string, string[]>; message?: string };
      const firstError = Object.values(payload.errors ?? {})[0]?.[0];

      return firstError ?? payload.message ?? 'Datos inválidos.';
    }

    if (error.status === 429) {
      return 'Demasiadas solicitudes. Espera un momento.';
    }

    return 'Ocurrió un error al guardar el producto.';
  }
}
