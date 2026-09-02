<?php

namespace App\Http\Controllers;

use App\Http\Requests\Product\StoreProductRequest;
use App\Http\Requests\Product\UpdateProductRequest;
use App\Http\Resources\ProductResource;
use App\Services\ExportService;
use App\Services\ProductService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ProductController extends Controller
{
    public function __construct(
        private readonly ProductService $productService,
        private readonly ExportService $exportService,
    ) {}

    public function index(Request $request)
    {
        $perPage = (int) $request->query('per_page', 15);
        $products = $this->productService->paginate($perPage);

        return ProductResource::collection($products);
    }

    public function store(StoreProductRequest $request): ProductResource
    {
        $product = $this->productService->create($request->validated());

        return new ProductResource($product);
    }

    public function show(string $product): ProductResource
    {
        return new ProductResource($this->productService->find($product));
    }

    public function update(UpdateProductRequest $request, string $product): ProductResource
    {
        $model = $this->productService->find($product);
        $updated = $this->productService->update($model, $request->validated());

        return new ProductResource($updated);
    }

    public function destroy(string $product): JsonResponse
    {
        $model = $this->productService->find($product);
        $this->productService->delete($model);

        return response()->json(['message' => 'Producto eliminado.']);
    }

    public function export(string $format): Response
    {
        return $this->exportService->products($format);
    }
}
