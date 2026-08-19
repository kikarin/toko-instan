<?php

namespace App\Gateways;

use App\Contracts\ShippingGateway;
use App\DTO\ShippingRate;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class BiteshipGateway implements ShippingGateway
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
        $key = (string) config('services.biteship.key');
        if ($key === '') {
            throw new RuntimeException('BITESHIP_API_KEY belum dikonfigurasi.');
        }

        $originPostal = $originPostalCode ?: (string) config('services.shipping.origin_postal_code', '12190');
        $destPostal = $destinationPostalCode ?: '';

        if ($destPostal === '') {
            throw new RuntimeException('Kode pos tujuan diperlukan untuk Biteship.');
        }

        $couriers = str_replace(':', ',', (string) config('services.shipping.couriers', 'jne,jnt,sicepat'));

        try {
            $response = Http::withToken($key)
                ->acceptJson()
                ->asJson()
                ->timeout(20)
                ->post(rtrim((string) config('services.biteship.base_url'), '/').'/v1/rates', [
                    'origin_postal_code' => (int) $originPostal,
                    'destination_postal_code' => (int) $destPostal,
                    'couriers' => $couriers,
                    'items' => [[
                        'name' => 'Checkout',
                        'description' => $originCity.' → '.$destinationCity,
                        'value' => 100000,
                        'quantity' => 1,
                        'weight' => max(1, $weightGram),
                    ]],
                ])
                ->throw()
                ->json();
        } catch (RequestException $e) {
            throw new RuntimeException('Gagal cek ongkir Biteship: '.$e->getMessage(), 0, $e);
        }

        $rates = [];

        foreach ($response['pricing'] ?? [] as $row) {
            $rates[] = new ShippingRate(
                courier: strtolower((string) ($row['courier_code'] ?? '')),
                service: (string) ($row['courier_service_code'] ?? $row['courier_service_name'] ?? ''),
                name: trim(($row['courier_name'] ?? '').' '.($row['courier_service_name'] ?? '')),
                cost: (int) ($row['price'] ?? 0),
                etd: (string) ($row['duration'] ?? $row['shipment_duration_range'] ?? '-'),
                description: (string) ($row['description'] ?? ''),
            );
        }

        return $rates;
    }
}
