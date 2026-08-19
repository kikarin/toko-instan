<?php

namespace App\Http\Controllers;

use App\Actions\UploadProductImage;
use App\Models\Product;
use App\Models\Store;
use App\Services\ReviewService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use RuntimeException;

class ReviewController extends Controller
{
    public function __construct(
        protected ReviewService $reviewService,
        protected UploadProductImage $uploadProductImage,
    ) {}

    public function store(Request $request, string $storeSlug, string $productSlug): RedirectResponse
    {
        $store = Store::query()->where('slug', $storeSlug)->firstOrFail();
        $product = Product::query()->where('store_id', $store->id)->where('slug', $productSlug)->firstOrFail();
        $validated = $request->validate([
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'body' => ['nullable', 'string', 'max:2000'],
            'order_item_id' => ['nullable', 'integer'],
            'photo' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:4096'],
        ]);

        $photoPath = null;
        if ($request->hasFile('photo')) {
            $uploaded = ($this->uploadProductImage)($request->file('photo'), 'reviews');
            $photoPath = $uploaded['path'];
        }

        try {
            $this->reviewService->create(
                $request->user(),
                $product,
                (int) $validated['rating'],
                $validated['body'] ?? null,
                $photoPath,
                isset($validated['order_item_id']) ? (int) $validated['order_item_id'] : null,
            );
        } catch (RuntimeException $e) {
            throw ValidationException::withMessages(['rating' => $e->getMessage()]);
        }

        return back()->with('success', 'Ulasan tersimpan.');
    }
}
