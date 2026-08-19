<?php

namespace App\Actions;

use App\Services\ImageVariantService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;

class UploadProductImage
{
    public function __construct(
        protected ImageVariantService $imageVariantService
    ) {}

    /**
     * Store original on R2, then resize via ProcessImageVariants job into 3 sizes.
     *
     * @return array{path: string, url: string, urls: array{thumbnail: string, medium: string, large: string}}
     */
    public function __invoke(UploadedFile $file, string $directory = 'products'): array
    {
        $uuid = (string) Str::uuid();
        $ext = strtolower($file->getClientOriginalExtension() ?: 'jpg');
        $originalPath = $directory.'/'.date('Y/m').'/'.$uuid.'_original.'.$ext;

        $stored = Storage::disk('r2')->putFileAs(
            dirname($originalPath),
            $file,
            basename($originalPath)
        );

        if ($stored === false) {
            throw new RuntimeException('Upload gagal.');
        }

        $binary = $file->get() ?: (string) file_get_contents($file->getRealPath());
        $variants = $this->imageVariantService->generateFromBinary($binary, dirname($originalPath));

        return [
            'path' => $variants['paths']['medium'],
            'url' => $variants['medium'],
            'urls' => [
                'thumbnail' => $variants['thumbnail'],
                'medium' => $variants['medium'],
                'large' => $variants['large'],
            ],
        ];
    }
}
