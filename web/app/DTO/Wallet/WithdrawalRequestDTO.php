<?php

namespace App\DTO\Wallet;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class WithdrawalRequestDTO
{
    public function __construct(
        public float $amount,
        public string $bankName,
        public string $accountNumber,
        public string $accountName
    ) {}

    public static function fromRequest(Request $request): self
    {
        $validated = Validator::make($request->all(), [
            'amount' => 'required|numeric|min:6000',
            'bank_name' => 'required|string|max:100',
            'account_number' => 'required|string|max:50',
            'account_name' => 'required|string|max:100',
        ])->validate();

        return new self(
            amount: (float) $validated['amount'],
            bankName: $validated['bank_name'],
            accountNumber: $validated['account_number'],
            accountName: $validated['account_name']
        );
    }

    public function toArray(): array
    {
        return [
            'amount' => $this->amount,
            'bank_name' => $this->bankName,
            'account_number' => $this->accountNumber,
            'account_name' => $this->accountName,
        ];
    }
}
