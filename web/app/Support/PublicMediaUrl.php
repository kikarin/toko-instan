<?php

namespace App\Support;

class PublicMediaUrl
{
    /**
     * Rewrite blocked R2 public-dev hosts (*.r2.dev) to the app media proxy.
     */
    public static function rewrite(?string $url): ?string
    {
        if ($url === null || $url === '') {
            return $url;
        }

        $host = parse_url($url, PHP_URL_HOST) ?: '';

        if ($host === '' || ! str_ends_with(strtolower($host), '.r2.dev')) {
            return $url;
        }

        $path = ltrim((string) parse_url($url, PHP_URL_PATH), '/');

        if ($path === '') {
            return $url;
        }

        return url('/media/'.$path);
    }
}
