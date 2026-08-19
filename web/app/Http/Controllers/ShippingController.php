<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Store;
use App\Services\ShippingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ShippingController extends Controller
{
    public function __construct(
        protected ShippingService $shippingService,
    ) {}

    public function quote(Request $request, string $storeSlug): JsonResponse
    {
        $items = collect($request->input('items', []))
            ->map(fn ($item) => [
                'id' => (int) ($item['id'] ?? 0),
                'qty' => max(1, (int) ($item['qty'] ?? 1)),
            ])
            ->filter(fn ($item) => $item['id'] > 0)
            ->values()
            ->all();

        $request->merge([
            'destination_city' => trim((string) $request->input('destination_city', '')),
            'destination_postal_code' => $request->filled('destination_postal_code')
                ? substr((string) $request->input('destination_postal_code'), 0, 10)
                : null,
            'items' => $items,
        ]);

        $validated = $request->validate([
            'destination_city' => ['required', 'string', 'max:120'],
            'destination_postal_code' => ['nullable', 'string', 'max:10'],
            'items' => ['nullable', 'array'],
            'items.*.id' => ['nullable', 'integer', 'min:1'],
            'items.*.qty' => ['nullable', 'integer', 'min:1'],
        ]);

        $store = Store::query()->where('slug', $storeSlug)->firstOrFail();
        $items = $validated['items'] ?? [];

        $foundProducts = collect($items)
            ->map(fn (array $item) => Product::query()->find($item['id'] ?? 0))
            ->filter();

        $allDigital = $foundProducts->isNotEmpty()
            && $foundProducts->every(fn (Product $product) => $product->isDigital());

        if ($allDigital) {
            return response()->json([
                'rates' => [],
                'weight_gram' => 0,
                'digital_only' => true,
            ]);
        }

        $quoteItems = $items !== [] ? $items : [['id' => 0, 'qty' => 1]];

        $rates = $this->shippingService->quoteForStore(
            $store,
            $validated['destination_city'],
            $quoteItems,
            $validated['destination_postal_code'] ?? null,
        );

        return response()->json([
            'rates' => array_map(fn ($rate) => $rate->toArray(), $rates),
            'weight_gram' => $this->shippingService->weightGrams($items !== [] ? $items : []),
        ]);
    }
}
