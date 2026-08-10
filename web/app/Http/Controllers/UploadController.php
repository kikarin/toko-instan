<?php

namespace App\Http\Controllers;

use App\Actions\UploadProductImage;
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
        $request->validate([
            'file' => 'required|image|mimes:jpeg,png,webp|max:2048',
        ]);

        try {
            $uploaded = ($this->uploadProductImage)($request->file('file'));
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
