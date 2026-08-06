<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class BuyerUserSeeder extends Seeder
{
    /**
     * Seed buyer users.
     */
    public function run(): void
    {
        $password = Hash::make('Sewdaq123');

        $buyers = [
            ['name' => 'Budi Santoso', 'email' => 'budi@example.com'],
            ['name' => 'Siti Rahma', 'email' => 'siti@example.com'],
            ['name' => 'Aditya Pratama', 'email' => 'aditya@example.com'],
            ['name' => 'Dewi Lestari', 'email' => 'dewi@example.com'],
            ['name' => 'Rizky Febrian', 'email' => 'rizky@example.com'],
            ['name' => 'Maya Indah', 'email' => 'maya@example.com'],
        ];

        foreach ($buyers as $buyer) {
            User::firstOrCreate(
                ['email' => $buyer['email']],
                [
                    'name' => $buyer['name'],
                    'password' => $password,
                    'role' => 'buyer',
                    'auth_provider' => 'email',
                    'email_verified_at' => now(),
                ]
            );
        }
    }
}
