<?php

namespace App\Http\Controllers\Api;

use App\DTO\ProductData;
use App\Http\Controllers\Controller;
use App\Services\ProductService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductApiController extends Controller
{
    public function __construct(protected ProductService $productService) {}

    public function index(Request $request): JsonResponse
    {
        $products = $this->productService->listForSeller($request->user()->id)
            ->map(fn ($p) => $this->productService->format($p))
            ->values();

        return response()->json(['data' => $products]);
    }

    public function store(Request $request): JsonResponse
    {
        $product = $this->productService->create(ProductData::fromRequest($request), $request->user()->id);

        return response()->json(['data' => $this->productService->format($product)], 201);
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $product = $this->productService->findForSeller($id, $request->user()->id);
        abort_unless($product, 404);

        return response()->json(['data' => $this->productService->format($product)]);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $product = $this->productService->findForSeller($id, $request->user()->id);
        abort_unless($product, 404);
        $this->productService->update($product, ProductData::fromRequest($request), $request->user()->id);

        return response()->json(['data' => $this->productService->format($product->fresh())]);
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $product = $this->productService->findForSeller($id, $request->user()->id);
        abort_unless($product, 404);
        $this->productService->delete($product, $request->user()->id);

        return response()->json(['ok' => true]);
    }
}
