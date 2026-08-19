<?php

namespace App\Http\Controllers;

use App\Actions\UploadDigitalProductFile;
use App\Actions\UploadProductImage;
use App\DTO\UploadDigitalFileDTO;
use App\DTO\UploadFileDTO;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use RuntimeException;

class UploadController extends Controller
{
    public function __construct(
        protected UploadProductImage $uploadProductImage,
        protected UploadDigitalProductFile $uploadDigitalProductFile
    ) {}

    public function store(Request $request): JsonResponse
    {
        $dto = UploadFileDTO::fromRequest($request);

        try {
            $uploaded = ($this->uploadProductImage)($dto->file, $dto->directory);
        } catch (RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }

        return response()->json([
            'url' => $uploaded['url'],
            'path' => $uploaded['path'],
            'urls' => $uploaded['urls'],
        ]);
    }

    public function storeDigital(Request $request): JsonResponse
    {
        $dto = UploadDigitalFileDTO::fromRequest($request);

        try {
            $uploaded = ($this->uploadDigitalProductFile)($dto->file);
        } catch (RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }

        return response()->json([
            'path' => $uploaded['path'],
            'name' => $uploaded['name'],
            'mime' => $uploaded['mime'],
            'size' => $uploaded['size'],
        ]);
    }
}
