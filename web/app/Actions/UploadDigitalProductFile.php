<?php

namespace App\Actions;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;

class UploadDigitalProductFile
{
    /**
     * Store a digital product file on R2 (private path for authenticated download).
     *
     * @return array{path: string, name: string, mime: string, size: int}
     */
    public function __invoke(UploadedFile $file, string $directory = 'digital'): array
    {
        $uuid = (string) Str::uuid();
        $originalName = $file->getClientOriginalName();
        $ext = strtolower($file->getClientOriginalExtension() ?: 'bin');
        $path = $directory.'/'.date('Y/m').'/'.$uuid.'.'.$ext;

        $stored = Storage::disk('r2')->putFileAs(
            dirname($path),
            $file,
            basename($path)
        );

        if ($stored === false) {
            throw new RuntimeException('Upload file digital gagal.');
        }

        return [
            'path' => $path,
            'name' => $originalName,
            'mime' => $file->getMimeType() ?: 'application/octet-stream',
            'size' => (int) $file->getSize(),
        ];
    }
}
