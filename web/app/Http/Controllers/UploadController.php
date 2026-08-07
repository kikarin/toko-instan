<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class UploadController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'file' => 'required|image|mimes:jpeg,png,webp|max:2048',
        ]);

        $path = $request->file('file')->store(
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
