<?php

namespace App\Http\Controllers;

use App\Actions\UploadProductImage;
use App\DTO\UploadFileDTO;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use RuntimeException;

class UploadController extends Controller
{
    public function __construct(
        protected UploadProductImage $uploadProductImage
    ) {}

    public function store(Request $request): JsonResponse
    {
        $dto = UploadFileDTO::fromRequest($request);

        try {
            $uploaded = ($this->uploadProductImage)($dto->file);
        } catch (RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }

        return response()->json([
            'url' => $uploaded['url'],
            'path' => $uploaded['path'],
            'urls' => $uploaded['urls'],
        ]);
    }
}
