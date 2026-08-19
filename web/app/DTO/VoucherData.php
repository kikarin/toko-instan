<?php

namespace App\DTO;

use App\Enums\VoucherType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class VoucherData
{
    public function __construct(
        public string $code,
        public string $name,
        public VoucherType $type,
        public int $value,
        public int $minSpend,
        public ?int $maxDiscount,
        public ?int $usageLimit,
        public ?string $startsAt,
        public ?string $expiresAt,
        public bool $isActive,
    ) {}

    public static function fromRequest(Request $request): self
    {
        $validated = Validator::make($request->all(), [
            'code' => ['required', 'string', 'max:40'],
            'name' => ['required', 'string', 'max:120'],
            'type' => ['required', Rule::enum(VoucherType::class)],
            'value' => ['required', 'integer', 'min:1'],
            'min_spend' => ['nullable', 'integer', 'min:0'],
            'max_discount' => ['nullable', 'integer', 'min:0'],
            'usage_limit' => ['nullable', 'integer', 'min:1'],
            'starts_at' => ['nullable', 'date'],
            'expires_at' => ['nullable', 'date', 'after_or_equal:starts_at'],
            'is_active' => ['nullable', 'boolean'],
        ])->after(function ($validator) use ($request) {
            if ($request->input('type') === VoucherType::Percent->value && (int) $request->input('value') > 100) {
                $validator->errors()->add('value', 'Diskon persen maksimal 100.');
            }
        })->validate();

        return new self(
            code: strtoupper(trim($validated['code'])),
            name: $validated['name'],
            type: VoucherType::from($validated['type']),
            value: (int) $validated['value'],
            minSpend: (int) ($validated['min_spend'] ?? 0),
            maxDiscount: isset($validated['max_discount']) ? (int) $validated['max_discount'] : null,
            usageLimit: isset($validated['usage_limit']) ? (int) $validated['usage_limit'] : null,
            startsAt: $validated['starts_at'] ?? null,
            expiresAt: $validated['expires_at'] ?? null,
            isActive: (bool) ($validated['is_active'] ?? true),
        );
    }
}
