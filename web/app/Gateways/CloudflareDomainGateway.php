<?php

namespace App\Gateways;

use App\Contracts\DomainGateway;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class CloudflareDomainGateway implements DomainGateway
{
    public function provisionHostname(string $hostname): array
    {
        $token = (string) config('services.cloudflare.token');
        $zone = (string) config('services.cloudflare.zone_id');

        if ($token === '' || $zone === '') {
            return [
                'status' => 'pending',
                'message' => 'Cloudflare belum dikonfigurasi. Arahkan CNAME domain ke platform, lalu status bisa diaktifkan manual.',
            ];
        }

        $response = Http::withToken($token)
            ->acceptJson()
            ->timeout(20)
            ->post('https://api.cloudflare.com/client/v4/zones/'.$zone.'/custom_hostnames', [
                'hostname' => $hostname,
                'ssl' => ['method' => 'http', 'type' => 'dv'],
            ]);

        if (! $response->successful()) {
            throw new RuntimeException('Cloudflare gagal: '.$response->status().' '.$response->body());
        }

        $ok = (bool) data_get($response->json(), 'success', false);

        return [
            'status' => $ok ? 'active' : 'pending',
            'message' => $ok ? 'Hostname didaftarkan di Cloudflare.' : 'Menunggu verifikasi DNS/SSL.',
        ];
    }
}
