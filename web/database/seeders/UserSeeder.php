<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Seed the seedable users. Tenants and Stores are seeded in ProductSeeder.
     */
    public function run(): void
    {
        // 1. Seed Buyer A (Nike Customer)
        $buyerA = User::firstOrCreate(
            ['email' => 'buyer@gmail.com'],
            [
                'name' => 'Niko Agustio',
                'username' => 'nikoagustio',
                'bio' => 'Pecinta produk Nike & sneakers original',
                'phone' => '+6285264415051',
                'phone_verified_at' => now(),
                'gender' => 'Pria',
                'birth_date' => '01 January 1991',
                'password' => Hash::make('Sewdaq123'),
                'role' => 'buyer',
                'auth_provider' => 'email',
                'email_verified_at' => now(),
                'avatar' => 'https://images.unsplash.com/photo-1534528741775-53994a69daeb?w=200&h=200&fit=crop&auto=format',
            ]
        );
        if ($buyerA->role !== 'buyer') {
            $buyerA->update(['role' => 'buyer']);
        }

        if ($buyerA->addresses()->count() === 0) {
            $buyerA->addresses()->create([
                'label' => 'Rumah',
                'recipient_name' => 'Niko Agustio',
                'phone' => '+6285264415051',
                'address' => 'Jl. Merdeka No. 123, RT 01/RW 02',
                'city' => 'Jakarta Selatan',
                'province' => 'DKI Jakarta',
                'postal_code' => '12190',
                'is_default' => true,
            ]);
        }

        // 2. Seed Buyer B (Adidas Customer)
        $buyerB = User::firstOrCreate(
            ['email' => 'buyer2@gmail.com'],
            [
                'name' => 'Budi Santoso',
                'username' => 'budisantoso',
                'bio' => 'Adidas 4 Lyfe',
                'phone' => '+6281234567890',
                'phone_verified_at' => now(),
                'gender' => 'Pria',
                'birth_date' => '02 February 1992',
                'password' => Hash::make('Sewdaq123'),
                'role' => 'buyer',
                'auth_provider' => 'email',
                'email_verified_at' => now(),
                'avatar' => 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=200&h=200&fit=crop&auto=format',
            ]
        );
        if ($buyerB->role !== 'buyer') {
            $buyerB->update(['role' => 'buyer']);
        }

        if ($buyerB->addresses()->count() === 0) {
            $buyerB->addresses()->create([
                'label' => 'Kantor',
                'recipient_name' => 'Budi Santoso',
                'phone' => '+6281234567890',
                'address' => 'Gedung Sudirman Lantai 4, Jl. Jend Sudirman',
                'city' => 'Jakarta Pusat',
                'province' => 'DKI Jakarta',
                'postal_code' => '10220',
                'is_default' => true,
            ]);
        }

        // 3. Seed Seller A (Nike)
        User::firstOrCreate(
            ['email' => 'seller.nike@gmail.com'],
            [
                'name' => 'Nike Official Manager',
                'username' => 'nike_official',
                'password' => Hash::make('Sewdaq123'),
                'role' => 'seller',
                'auth_provider' => 'email',
                'email_verified_at' => now(),
                'avatar' => 'https://images.unsplash.com/photo-1542291026-7eec264c27ff?w=200&h=200&fit=crop&auto=format',
            ]
        );

        // 4. Seed Seller B (Adidas)
        User::firstOrCreate(
            ['email' => 'seller.adidas@gmail.com'],
            [
                'name' => 'Adidas Official Manager',
                'username' => 'adidas_official',
                'password' => Hash::make('Sewdaq123'),
                'role' => 'seller',
                'auth_provider' => 'email',
                'email_verified_at' => now(),
                'avatar' => 'https://images.unsplash.com/photo-1518002171953-a080ee817e1f?w=200&h=200&fit=crop&auto=format',
            ]
        );

        // 5. Seed Admin User
        User::firstOrCreate(
            ['email' => 'admin@gmail.com'],
            [
                'name' => 'Nike Admin Master',
                'username' => 'admin_nike',
                'password' => Hash::make('Sewdaq123'),
                'role' => 'admin',
                'auth_provider' => 'email',
                'email_verified_at' => now(),
            ]
        );
    }
}
