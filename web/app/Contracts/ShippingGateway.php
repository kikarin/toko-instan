<?php

namespace App\Contracts;

use App\DTO\ShippingRate;

interface ShippingGateway
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
    ): array;
}
