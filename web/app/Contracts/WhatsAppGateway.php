<?php

namespace App\Contracts;

interface WhatsAppGateway
{
    public function sendText(string $toE164, string $body): void;
}
