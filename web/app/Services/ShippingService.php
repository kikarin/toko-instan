<?php

namespace App\Services;

use App\Contracts\ShippingGateway;
use App\DTO\ShippingRate;
use App\Gateways\BiteshipGateway;
use App\Gateways\FallbackShippingGateway;
use App\Gateways\RajaOngkirGateway;
use App\Models\Product;
use App\Models\Store;
use RuntimeException;
use Throwable;

class ShippingService
{
    public function __construct(
        protected RajaOngkirGateway $rajaOngkir,
        protected BiteshipGateway $biteship,
        protected FallbackShippingGateway $fallback,
    ) {}

    public function gateway(): ShippingGateway
    {
        $provider = (string) config('services.shipping.provider', 'rajaongkir');

        if ($provider === 'biteship' && filled(config('services.biteship.key'))) {
            return $this->biteship;
        }

        if (filled(config('services.rajaongkir.key'))) {
            return $this->rajaOngkir;
        }

        return $this->fallback;
    }

    /**
     * @param  list<array{id?: int, qty?: int}>  $items
     * @return list<ShippingRate>
     */
    public function quoteForStore(Store $store, string $destinationCity, array $items, ?string $destinationPostalCode = null): array
    {
        $weight = $this->weightGrams($items);
        $originCity = $store->origin_city ?: (string) config('services.shipping.origin_city', 'Jakarta Selatan');
        $originPostal = $store->origin_postal_code ?: (string) config('services.shipping.origin_postal_code', '12190');

        try {
            $rates = $this->gateway()->quote(
                $originCity,
                $destinationCity,
                $weight,
                $originPostal,
                $destinationPostalCode,
            );
        } catch (Throwable $e) {
            report($e);
            $rates = $this->fallback->quote($originCity, $destinationCity, $weight, $originPostal, $destinationPostalCode);
        }

        if ($rates === []) {
            $rates = $this->fallback->quote($originCity, $destinationCity, $weight, $originPostal, $destinationPostalCode);
        }

        return $rates;
    }

    /**
     * @param  list<ShippingRate>  $rates
     */
    public function pickRate(array $rates, ?string $rateId, ?string $courierName): ?ShippingRate
    {
        if ($rateId) {
            foreach ($rates as $rate) {
                if ($rate->courier.':'.$rate->service === $rateId) {
                    return $rate;
                }
            }
        }

        if ($courierName) {
            $needle = mb_strtolower($courierName);
            foreach ($rates as $rate) {
                if (str_contains(mb_strtolower($rate->name), $needle) || str_contains($needle, mb_strtolower($rate->courier))) {
                    return $rate;
                }
            }
        }

        return $rates[0] ?? null;
    }

    public function resolveCost(Store $store, string $destinationCity, array $items, ?string $rateId, ?string $courierName, ?string $destinationPostalCode = null): int
    {
        if ($destinationCity === '') {
            return 15000;
        }

        $rate = $this->pickRate(
            $this->quoteForStore($store, $destinationCity, $items, $destinationPostalCode),
            $rateId,
            $courierName,
        );

        if (! $rate) {
            throw new RuntimeException('Ongkir tidak tersedia untuk kota tujuan.');
        }

        return $rate->cost;
    }

    /**
     * @param  list<array{id?: int, qty?: int}>  $items
     */
    public function weightGrams(array $items): int
    {
        $total = 0;

        foreach ($items as $item) {
            $product = isset($item['id']) ? Product::query()->find((int) $item['id']) : null;
            $unit = max(100, (int) ($product?->weight_gram ?: 500));
            $total += $unit * max(1, (int) ($item['qty'] ?? 1));
        }

        return max(100, $total);
    }
}
