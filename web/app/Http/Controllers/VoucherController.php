<?php

namespace App\Http\Controllers;

use App\DTO\VoucherData;
use App\Models\Voucher;
use App\Services\StoreService;
use App\Services\VoucherService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;
use RuntimeException;

class VoucherController extends Controller
{
    public function __construct(
        protected VoucherService $voucherService,
        protected StoreService $storeService,
    ) {}

    public function index(Request $request): Response
    {
        $store = $this->storeService->getActiveStore($request->user()->id);

        $vouchers = $store
            ? collect($this->voucherService->listForStore($store))->map(fn (Voucher $v) => [
                'id' => $v->id,
                'code' => $v->code,
                'name' => $v->name,
                'type' => $v->type->value,
                'value' => $v->value,
                'min_spend' => $v->min_spend,
                'max_discount' => $v->max_discount,
                'usage_limit' => $v->usage_limit,
                'used_count' => $v->used_count,
                'starts_at' => $v->starts_at?->format('Y-m-d'),
                'expires_at' => $v->expires_at?->format('Y-m-d'),
                'is_active' => $v->is_active,
            ])->values()
            : collect();

        return Inertia::render('Vouchers/Index', [
            'vouchers' => $vouchers,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $store = $this->storeService->getActiveStore($request->user()->id)
            ?? throw ValidationException::withMessages(['code' => 'Toko tidak ditemukan.']);

        try {
            $this->voucherService->create($store, VoucherData::fromRequest($request));
        } catch (RuntimeException $e) {
            throw ValidationException::withMessages(['code' => $e->getMessage()]);
        }

        return back()->with('success', 'Voucher dibuat.');
    }

    public function update(Request $request, int $id): RedirectResponse
    {
        $voucher = $this->owned($request, $id);

        try {
            $this->voucherService->update($voucher, VoucherData::fromRequest($request));
        } catch (RuntimeException $e) {
            throw ValidationException::withMessages(['code' => $e->getMessage()]);
        }

        return back()->with('success', 'Voucher diperbarui.');
    }

    public function destroy(Request $request, int $id): RedirectResponse
    {
        $this->voucherService->delete($this->owned($request, $id));

        return back()->with('success', 'Voucher dihapus.');
    }

    protected function owned(Request $request, int $id): Voucher
    {
        $store = $this->storeService->getActiveStore($request->user()->id);

        return Voucher::query()
            ->where('store_id', $store?->id)
            ->findOrFail($id);
    }
}
