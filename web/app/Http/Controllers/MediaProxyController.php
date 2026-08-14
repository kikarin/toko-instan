<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class MediaProxyController extends Controller
{
    /**
     * @var list<string>
     */
    private const ALLOWED_PREFIXES = [
        'products/',
        'stores/',
        'cms/',
        'reviews/',
        'blog/',
        'misc/',
    ];

    public function show(Request $request, string $path): StreamedResponse
    {
        $path = ltrim($path, '/');

        if ($path === '' || str_contains($path, '..')) {
            abort(404);
        }

        $allowed = false;
        foreach (self::ALLOWED_PREFIXES as $prefix) {
            if (str_starts_with($path, $prefix)) {
                $allowed = true;
                break;
            }
        }

        if (! $allowed) {
            abort(404);
        }

        $disk = Storage::disk('r2');

        if (! $disk->exists($path)) {
            abort(404);
        }

        $mime = $disk->mimeType($path) ?: 'application/octet-stream';

        return $disk->response($path, basename($path), [
            'Content-Type' => $mime,
            'Cache-Control' => 'public, max-age=86400',
        ]);
    }
}
