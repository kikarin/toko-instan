<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Format;
use Intervention\Image\Interfaces\ImageManagerInterface;
use RuntimeException;

class ImageVariantService
{
    /**
     * @var array<string, int>
     */
    protected array $widths = [
        'thumbnail' => 150,
        'medium' => 600,
        'large' => 1200,
    ];

    public function __construct(
        protected ImageManagerInterface $images
    ) {}

    /**
     * Generate thumbnail / medium / large WebP variants from an R2 original path.
     *
     * @return array{thumbnail: string, medium: string, large: string, paths: array{thumbnail: string, medium: string, large: string}}
     */
    public function generateFromR2Path(string $originalPath): array
    {
        $disk = Storage::disk('r2');

        if (! $disk->exists($originalPath)) {
            throw new RuntimeException("File original tidak ditemukan: {$originalPath}");
        }

        $binary = $disk->get($originalPath);
        if ($binary === null || $binary === '') {
            throw new RuntimeException('Gagal membaca file original dari R2.');
        }

        $directory = trim(dirname($originalPath), '.');
        $uuid = (string) Str::uuid();

        $urls = [];
        $paths = [];

        foreach ($this->widths as $variant => $width) {
            $encoded = $this->images
                ->decodeBinary($binary)
                ->scaleDown(width: $width)
                ->encodeUsingFormat(Format::WEBP, quality: 80);

            $path = ($directory !== '' ? $directory.'/' : '').$uuid.'_'.$variant.'.webp';
            $disk->put($path, (string) $encoded, 'public');

            $paths[$variant] = $path;
            $urls[$variant] = $disk->url($path);
        }

        return [
            'thumbnail' => $urls['thumbnail'],
            'medium' => $urls['medium'],
            'large' => $urls['large'],
            'paths' => $paths,
        ];
    }
}
