<?php

namespace App\Http\Controllers;

use App\Models\Store;
use App\Services\TaxService;
use App\Services\VoucherService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use RuntimeException;

class CheckoutVoucherController extends Controller
{
    public function __construct(protected VoucherService $voucherService) {}

    public function preview(Request $request, string $storeSlug): JsonResponse
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'max:40'],
            'subtotal' => ['required', 'integer', 'min:0'],
        ]);

        $store = Store::query()->where('slug', $storeSlug)->firstOrFail();

        try {
            $result = $this->voucherService->preview($store, $validated['code'], (int) $validated['subtotal']);
        } catch (RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        $tax = app(TaxService::class)->ppnAmount(
            $store,
            max(0, (int) $validated['subtotal'] - $result['discount']),
        );

        return response()->json([
            'code' => $result['voucher']->code,
            'name' => $result['voucher']->name,
            'discount' => $result['discount'],
            'tax' => $tax,
        ]);
    }
}
