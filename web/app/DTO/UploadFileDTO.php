<?php

namespace App\DTO;

use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Validator;

class UploadFileDTO
{
    public function __construct(
        public UploadedFile $file
    ) {}

    public static function fromRequest(Request $request): self
    {
        $validated = Validator::make($request->all(), [
            'file' => 'required|image|mimes:jpeg,png,webp|max:2048',
        ])->validate();

        return new self($validated['file']);
    }
}
