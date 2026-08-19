<?php

namespace App\DTO\Admin;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class UpdateRoleDTO
{
    public function __construct(
        public string $role
    ) {}

    public static function fromRequest(Request $request): self
    {
        $validated = Validator::make($request->all(), [
            'role' => ['required', 'string', 'in:buyer,seller,admin'],
        ])->validate();

        return new self(
            role: $validated['role']
        );
    }
}
