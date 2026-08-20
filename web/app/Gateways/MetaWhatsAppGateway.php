<?php

namespace App\Gateways;

use App\Contracts\WhatsAppGateway;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class MetaWhatsAppGateway implements WhatsAppGateway
{
    public function sendText(string $toE164, string $body): void
    {
        $token = (string) config('services.whatsapp.token');
        $phoneId = (string) config('services.whatsapp.phone_id');

        if ($token === '' || $phoneId === '') {
            throw new RuntimeException('WhatsApp API belum dikonfigurasi.');
        }

        $response = Http::withToken($token)
            ->acceptJson()
            ->timeout(20)
            ->post(rtrim((string) config('services.whatsapp.base_url'), '/').'/'.$phoneId.'/messages', [
                'messaging_product' => 'whatsapp',
                'to' => $toE164,
                'type' => 'text',
                'text' => ['body' => $body],
            ]);

        if (! $response->successful()) {
            throw new RuntimeException('WhatsApp gagal: '.$response->status());
        }
    }
}
