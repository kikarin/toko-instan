<?php

namespace App\Enums;

enum PaymentMethod: string
{
    case Qris = 'qris';
    case Va = 'va';
    case Ewallet = 'ewallet';
    case Transfer = 'transfer';
    case Cod = 'cod';

    public function isGateway(): bool
    {
        return in_array($this, [self::Qris, self::Va, self::Ewallet], true);
    }

    public function provider(): PaymentProvider
    {
        return match ($this) {
            self::Qris, self::Va, self::Ewallet => PaymentProvider::Midtrans,
            self::Transfer => PaymentProvider::Manual,
            self::Cod => PaymentProvider::Cod,
        };
    }
}
