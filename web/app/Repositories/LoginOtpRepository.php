<?php

namespace App\Repositories;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class LoginOtpRepository
{
    public const EXPIRY_MINUTES = 10;

    public function store(string $email, ?int $storeId, string $plainCode): void
    {
        DB::table('login_otps')->updateOrInsert(
            ['email' => $email, 'store_id' => $storeId],
            [
                'code' => hash('sha256', $plainCode),
                'expires_at' => now()->addMinutes(self::EXPIRY_MINUTES),
                'updated_at' => now(),
                'created_at' => now(),
            ]
        );
    }

    public function findValid(string $email, ?int $storeId, string $plainCode): ?object
    {
        $row = DB::table('login_otps')
            ->where('email', $email)
            ->where('store_id', $storeId)
            ->where('code', hash('sha256', $plainCode))
            ->first();

        if (! $row) {
            return null;
        }

        if (Carbon::parse($row->expires_at)->isPast()) {
            return null;
        }

        return $row;
    }

    public function deleteForEmail(string $email, ?int $storeId): void
    {
        DB::table('login_otps')
            ->where('email', $email)
            ->where('store_id', $storeId)
            ->delete();
    }
}
