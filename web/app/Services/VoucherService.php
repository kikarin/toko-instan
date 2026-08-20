<?php

namespace App\Services;

use App\DTO\VoucherData;
use App\Enums\VoucherType;
use App\Models\Store;
use App\Models\Voucher;
use RuntimeException;

class VoucherService
{
    /**
     * @return list<Voucher>
     */
    public function listForStore(Store $store): array
    {
        return Voucher::query()
            ->where('store_id', $store->id)
            ->orderByDesc('id')
            ->get()
            ->all();
    }

    public function create(Store $store, VoucherData $dto): Voucher
    {
        return Voucher::query()->create($this->payload($store, $dto));
    }

    public function update(Voucher $voucher, VoucherData $dto): Voucher
    {
        $voucher->update($this->payload($voucher->store, $dto, $voucher));

        return $voucher->refresh();
    }

    public function delete(Voucher $voucher): void
    {
        $voucher->delete();
    }

    /**
     * @return array{voucher: Voucher, discount: int}
     */
    public function preview(Store $store, string $code, int $subtotal): array
    {
        $voucher = Voucher::query()
            ->where('store_id', $store->id)
            ->whereRaw('upper(code) = ?', [strtoupper(trim($code))])
            ->first();

        if (! $voucher) {
            throw new RuntimeException('Kode voucher tidak ditemukan.');
        }

        if (! $voucher->is_active) {
            throw new RuntimeException('Voucher tidak aktif.');
        }

        if ($voucher->starts_at && $voucher->starts_at->isFuture()) {
            throw new RuntimeException('Voucher belum berlaku.');
        }

        if ($voucher->expires_at && $voucher->expires_at->isPast()) {
            throw new RuntimeException('Voucher sudah kedaluwarsa.');
        }

        if ($voucher->usage_limit !== null && $voucher->used_count >= $voucher->usage_limit) {
            throw new RuntimeException('Kuota voucher habis.');
        }

        if ($subtotal < $voucher->min_spend) {
            throw new RuntimeException('Belanja belum memenuhi minimum voucher.');
        }

        $discount = $this->discountAmount($voucher, $subtotal);

        if ($discount <= 0) {
            throw new RuntimeException('Voucher tidak menghasilkan diskon.');
        }

        return ['voucher' => $voucher, 'discount' => $discount];
    }

    public function redeem(Voucher $voucher): void
    {
        $voucher->increment('used_count');
    }

    public function discountAmount(Voucher $voucher, int $subtotal): int
    {
        $amount = $voucher->type === VoucherType::Percent
            ? (int) round($subtotal * $voucher->value / 100)
            : (int) $voucher->value;

        if ($voucher->max_discount !== null) {
            $amount = min($amount, (int) $voucher->max_discount);
        }

        return max(0, min($amount, $subtotal));
    }

    /**
     * @return array<string, mixed>
     */
    protected function payload(Store $store, VoucherData $dto, ?Voucher $existing = null): array
    {
        $codeTaken = Voucher::query()
            ->where('store_id', $store->id)
            ->where('code', $dto->code)
            ->when($existing, fn ($q) => $q->whereKeyNot($existing->id))
            ->exists();

        if ($codeTaken) {
            throw new RuntimeException('Kode voucher sudah dipakai.');
        }

        return [
            'tenant_id' => $store->tenant_id,
            'store_id' => $store->id,
            'code' => $dto->code,
            'name' => $dto->name,
            'type' => $dto->type,
            'value' => $dto->value,
            'min_spend' => $dto->minSpend,
            'max_discount' => $dto->maxDiscount,
            'usage_limit' => $dto->usageLimit,
            'starts_at' => $dto->startsAt,
            'expires_at' => $dto->expiresAt,
            'is_active' => $dto->isActive,
        ];
    }
}
