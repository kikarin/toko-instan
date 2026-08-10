<?php

namespace App\DTO\Wallet;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class WithdrawalRejectDTO
{
    public function __construct(
        public string $reason
    ) {}

    public static function fromRequest(Request $request): self
    {
        $validated = Validator::make($request->all(), [
            'reason' => 'required|string|max:500',
        ])->validate();

        return new self($validated['reason']);
    }
}
