<?php

namespace App\Enums;

enum ProductType: string
{
    case Physical = 'physical';
    case Digital = 'digital';

    public function label(): string
    {
        return match ($this) {
            self::Physical => 'Fisik',
            self::Digital => 'Digital',
        };
    }
}
