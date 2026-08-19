<?php

namespace App\Http\Controllers;

use App\Services\AiCopyService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use RuntimeException;

class ProductAiController extends Controller
{
    public function __construct(protected AiCopyService $aiCopyService) {}

    public function generate(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'category' => ['nullable', 'string', 'max:100'],
            'brand' => ['nullable', 'string', 'max:100'],
            'price' => ['nullable'],
            'description' => ['nullable', 'string'],
            'tasks' => ['nullable', 'array'],
            'tasks.*' => ['in:description,seo,caption'],
        ]);

        try {
            $copy = $this->aiCopyService->generateProductCopy($validated);
        } catch (RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        return response()->json($copy);
    }
}
