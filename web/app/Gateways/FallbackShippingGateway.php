<?php

namespace App\Gateways;

use App\Contracts\ShippingGateway;
use App\DTO\ShippingRate;

class FallbackShippingGateway implements ShippingGateway
{
    /**
     * @return list<ShippingRate>
     */
    public function quote(
        string $originCity,
        string $destinationCity,
        int $weightGram,
        ?string $originPostalCode = null,
        ?string $destinationPostalCode = null,
    ): array {
        $weightKg = max(1, (int) ceil(max(1, $weightGram) / 1000));
        $cityHash = abs(crc32(mb_strtolower($destinationCity ?: 'jakarta'))) % 8000;

        return [
            new ShippingRate('jne', 'REG', 'JNE Reguler', 15000 + $cityHash + (2000 * ($weightKg - 1)), '2-3', 'Layanan reguler'),
            new ShippingRate('jnt', 'EZ', 'J&T Express', 18000 + ($cityHash % 5000) + (2500 * ($weightKg - 1)), '1-2', 'EZ'),
            new ShippingRate('sicepat', 'BEST', 'SiCepat BEST', 22000 + ($cityHash % 4000) + (3000 * ($weightKg - 1)), '1', 'BEST'),
        ];
    }
}
