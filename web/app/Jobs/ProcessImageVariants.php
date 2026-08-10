<?php

namespace App\Jobs;

use App\Services\ImageVariantService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Cache;

class ProcessImageVariants implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public string $originalPath,
        public ?string $cacheKey = null
    ) {}

    /**
     * @return array{thumbnail: string, medium: string, large: string, paths: array{thumbnail: string, medium: string, large: string}}
     */
    public function handle(ImageVariantService $variants): array
    {
        $result = $variants->generateFromR2Path($this->originalPath);

        if ($this->cacheKey) {
            Cache::put($this->cacheKey, $result, now()->addHour());
        }

        return $result;
    }
}
