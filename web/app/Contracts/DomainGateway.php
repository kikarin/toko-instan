<?php

namespace App\Contracts;

interface DomainGateway
{
    /**
     * @return array{status: string, message: string}
     */
    public function provisionHostname(string $hostname): array;
}
