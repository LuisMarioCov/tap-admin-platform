<?php

namespace App\Services;

use App\Models\Product;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class ProductService
{
    public function __construct(
        private readonly CodeGeneratorService $codeGenerator,
        private readonly AuditLogger $auditLogger,
    ) {}

    public function paginate(int $perPage = 15): LengthAwarePaginator
    {
        return Product::query()
            ->orderByDesc('created_at')
            ->paginate(min($perPage, 100));
    }

    public function find(string $id): Product
    {
        $product = Product::query()->find($id);

        if ($product === null) {
            throw new ModelNotFoundException('Producto no encontrado.');
        }

        return $product;
    }

    /** Assigns PRD-##### then writes an audit create row. */
    public function create(array $data): Product
    {
        $product = Product::query()->create([
            'code' => $this->codeGenerator->next('PRD'),
            'name' => $data['name'],
            'brand' => $data['brand'],
            'price' => $data['price'],
        ]);

        $this->auditLogger->record(
            'products',
            (string) $product->getKey(),
            'created',
            null,
            $product->toArray(),
        );

        return $product;
    }

    /** @param array{name: string, brand: string, price: int} $data */
    public function update(Product $product, array $data): Product
    {
        $before = $product->toArray();

        $product->fill([
            'name' => $data['name'],
            'brand' => $data['brand'],
            'price' => $data['price'],
        ]);
        $product->save();

        $this->auditLogger->record(
            'products',
            (string) $product->getKey(),
            'updated',
            $before,
            $product->fresh()->toArray(),
        );

        return $product->fresh();
    }

    public function delete(Product $product): void
    {
        $before = $product->toArray();
        $product->delete();

        $this->auditLogger->record(
            'products',
            (string) $product->getKey(),
            'deleted',
            $before,
            ['deleted_at' => now()->toIso8601String()],
        );
    }
}
