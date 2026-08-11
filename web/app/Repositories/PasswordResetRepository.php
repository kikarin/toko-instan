<?php

namespace App\Repositories;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class PasswordResetRepository
{
    public const TOKEN_EXPIRY_MINUTES = 60;

    public function store(string $email, ?int $storeId, string $token): void
    {
        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $email, 'store_id' => $storeId],
            [
                'token' => hash('sha256', $token),
                'created_at' => now(),
            ]
        );
    }

    public function findValid(string $email, ?int $storeId, string $token): ?object
    {
        $row = DB::table('password_reset_tokens')
            ->where('email', $email)
            ->where('store_id', $storeId)
            ->where('token', hash('sha256', $token))
            ->first();

        if (! $row) {
            return null;
        }

        $createdAt = Carbon::parse($row->created_at);

        if ($createdAt->addMinutes(self::TOKEN_EXPIRY_MINUTES)->isPast()) {
            return null;
        }

        return $row;
    }

    public function deleteForEmail(string $email, ?int $storeId): void
    {
        DB::table('password_reset_tokens')
            ->where('email', $email)
            ->where('store_id', $storeId)
            ->delete();
    }
}
