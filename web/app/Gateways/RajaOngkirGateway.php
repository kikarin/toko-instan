<?php

namespace App\Gateways;

use App\Contracts\ShippingGateway;
use App\DTO\ShippingRate;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use RuntimeException;
use Throwable;

class RajaOngkirGateway implements ShippingGateway
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
        $key = (string) config('services.rajaongkir.key');
        if ($key === '') {
            throw new RuntimeException('RAJAONGKIR_API_KEY belum dikonfigurasi.');
        }

        try {
            return $this->quoteKomerce($originCity, $destinationCity, $weightGram, $destinationPostalCode);
        } catch (Throwable $komerceError) {
            if ($this->usesKomerceOnly()) {
                throw $komerceError instanceof RuntimeException
                    ? $komerceError
                    : new RuntimeException('Gagal cek ongkir RajaOngkir: '.$komerceError->getMessage(), 0, $komerceError);
            }
        }

        return $this->quoteStarter($originCity, $destinationCity, $weightGram);
    }

    /**
     * @return list<ShippingRate>
     */
    protected function quoteKomerce(
        string $originCity,
        string $destinationCity,
        int $weightGram,
        ?string $destinationPostalCode,
    ): array {
        $originId = $this->findKomerceDestinationId($originCity, (string) config('services.shipping.origin_postal_code'));
        $destinationId = $this->findKomerceDestinationId($destinationCity, $destinationPostalCode);

        if ($originId === null || $destinationId === null) {
            throw new RuntimeException('Kota asal/tujuan tidak ditemukan di RajaOngkir.');
        }

        $couriers = explode(':', (string) config('services.shipping.couriers', 'jne:jnt:sicepat'));
        $rates = [];

        foreach ($couriers as $courier) {
            $courier = strtolower(trim($courier));
            if ($courier === '') {
                continue;
            }

            $response = Http::withHeaders(['key' => (string) config('services.rajaongkir.key')])
                ->asForm()
                ->acceptJson()
                ->timeout(20)
                ->post($this->komerceBase().'/calculate/domestic-cost', [
                    'origin' => $originId,
                    'destination' => $destinationId,
                    'weight' => max(1, $weightGram),
                    'courier' => $courier,
                ])
                ->throw()
                ->json();

            foreach ($response['data'] ?? [] as $row) {
                $rates[] = new ShippingRate(
                    courier: strtolower((string) ($row['code'] ?? $courier)),
                    service: (string) ($row['service'] ?? ''),
                    name: trim(($row['name'] ?? strtoupper($courier)).' '.($row['service'] ?? '')),
                    cost: (int) ($row['cost'] ?? 0),
                    etd: (string) ($row['etd'] ?? '-'),
                    description: (string) ($row['description'] ?? ''),
                );
            }
        }

        return $rates;
    }

    /**
     * @return list<ShippingRate>
     */
    protected function quoteStarter(string $originCity, string $destinationCity, int $weightGram): array
    {
        $originId = $this->findStarterCityId($originCity);
        $destinationId = $this->findStarterCityId($destinationCity);

        if ($originId === null || $destinationId === null) {
            throw new RuntimeException('Kota asal/tujuan tidak ditemukan di RajaOngkir.');
        }

        $couriers = explode(':', (string) config('services.shipping.couriers', 'jne:jnt:sicepat'));
        $rates = [];

        foreach ($couriers as $courier) {
            $courier = strtolower(trim($courier));
            if ($courier === '') {
                continue;
            }

            try {
                $response = Http::withHeaders(['key' => (string) config('services.rajaongkir.key')])
                    ->asForm()
                    ->acceptJson()
                    ->timeout(20)
                    ->post(rtrim((string) config('services.rajaongkir.base_url'), '/').'/cost', [
                        'origin' => $originId,
                        'destination' => $destinationId,
                        'weight' => max(1, $weightGram),
                        'courier' => $courier,
                    ])
                    ->throw()
                    ->json();
            } catch (RequestException $e) {
                throw new RuntimeException('Gagal cek ongkir RajaOngkir: '.$e->getMessage(), 0, $e);
            }

            foreach ($response['rajaongkir']['results'] ?? [] as $result) {
                $code = strtolower((string) ($result['code'] ?? $courier));
                $brand = (string) ($result['name'] ?? strtoupper($code));

                foreach ($result['costs'] ?? [] as $service) {
                    $cost = (int) ($service['cost'][0]['value'] ?? 0);
                    $etd = (string) ($service['cost'][0]['etd'] ?? '-');

                    $rates[] = new ShippingRate(
                        courier: $code,
                        service: (string) ($service['service'] ?? ''),
                        name: trim($brand.' '.($service['service'] ?? '')),
                        cost: $cost,
                        etd: $etd !== '' ? $etd : '-',
                        description: (string) ($service['description'] ?? ''),
                    );
                }
            }
        }

        return $rates;
    }

    protected function findKomerceDestinationId(string $cityName, ?string $postalCode): ?string
    {
        $search = trim($cityName.' '.($postalCode ?? ''));
        $cacheKey = 'rajaongkir.komerce.dest.'.md5(mb_strtolower($search));

        $rows = Cache::remember($cacheKey, 86400, function () use ($search) {
            $response = Http::withHeaders(['key' => (string) config('services.rajaongkir.key')])
                ->acceptJson()
                ->timeout(20)
                ->get($this->komerceBase().'/destination/domestic-destination', [
                    'search' => $search,
                    'limit' => 10,
                    'offset' => 0,
                ])
                ->throw()
                ->json();

            return $response['data'] ?? [];
        });

        $normalizedCity = $this->normalizeCity($cityName);

        foreach ($rows as $row) {
            $label = $this->normalizeCity((string) ($row['label'] ?? ''));
            $city = $this->normalizeCity((string) ($row['city_name'] ?? ''));
            $zip = (string) ($row['zip_code'] ?? '');

            if ($postalCode && $zip !== '' && $zip === $postalCode) {
                return (string) $row['id'];
            }

            if ($city === $normalizedCity || str_contains($label, $normalizedCity)) {
                return (string) $row['id'];
            }
        }

        return isset($rows[0]['id']) ? (string) $rows[0]['id'] : null;
    }

    public function findCityId(string $cityName): ?string
    {
        return $this->findStarterCityId($cityName);
    }

    protected function findStarterCityId(string $cityName): ?string
    {
        $normalized = $this->normalizeCity($cityName);

        foreach ($this->starterCities() as $city) {
            $candidate = $this->normalizeCity((string) ($city['city_name'] ?? ''));
            if ($candidate !== '' && ($candidate === $normalized || str_contains($normalized, $candidate) || str_contains($candidate, $normalized))) {
                return (string) $city['city_id'];
            }
        }

        return null;
    }

    /**
     * @return list<array<string, mixed>>
     */
    protected function starterCities(): array
    {
        $key = (string) config('services.rajaongkir.key');

        return Cache::remember('rajaongkir.cities', 86400, function () use ($key) {
            $response = Http::withHeaders(['key' => $key])
                ->acceptJson()
                ->timeout(20)
                ->get(rtrim((string) config('services.rajaongkir.base_url'), '/').'/city')
                ->throw()
                ->json();

            return $response['rajaongkir']['results'] ?? [];
        });
    }

    protected function usesKomerceOnly(): bool
    {
        return str_contains((string) config('services.rajaongkir.base_url'), 'komerce');
    }

    protected function komerceBase(): string
    {
        $configured = (string) config('services.rajaongkir.base_url');
        if (str_contains($configured, 'komerce')) {
            return rtrim($configured, '/');
        }

        return 'https://rajaongkir.komerce.id/api/v1';
    }

    protected function normalizeCity(string $name): string
    {
        $name = mb_strtolower(trim($name));
        $name = preg_replace('/^(kota|kabupaten|kab\.?)\s+/u', '', $name) ?? $name;

        return trim($name);
    }
}
