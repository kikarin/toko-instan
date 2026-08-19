<?php

namespace App\Contracts;

interface AiProvider
{
    /**
     * @param  array{temperature?: float, max_tokens?: int}  $options
     */
    public function complete(string $prompt, array $options = []): string;
}
