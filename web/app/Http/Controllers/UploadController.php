<?php

namespace App\Http\Controllers;

use App\DTO\UploadFileDTO;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class UploadController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $dto = UploadFileDTO::fromRequest($request);

        $path = $dto->file->store(
            'products/'.date('Y/m'),
            'r2'
        );

        if ($path === false) {
            return response()->json(['message' => 'Upload gagal.'], 500);
        }

        return response()->json([
            'url' => Storage::disk('r2')->url($path),
            'path' => $path,
        ]);
    }
}
