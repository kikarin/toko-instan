<?php

namespace Database\Seeders;

use App\Models\Order;
use App\Models\Product;
use App\Models\Store;
use App\Models\Tenant;
use App\Models\User;
use App\Models\Withdrawal;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Seed Main Merchant User
        $merchant = User::firstOrCreate(
            ['email' => 'owner@tokobagus.com'],
            [
                'name' => 'TokoBagus Owner',
                'password' => Hash::make('password123'),
                'auth_provider' => 'email',
                'email_verified_at' => now(),
            ]
        );

        // 1b. Seed Admin Master User
        User::firstOrCreate(
            ['email' => 'admin@toko-instan.com'],
            [
                'name' => 'Admin Master',
                'password' => Hash::make('password123'),
                'role' => 'admin',
                'auth_provider' => 'email',
                'email_verified_at' => now(),
            ]
        );

        // 2. Seed Tenant
        $tenant = Tenant::firstOrCreate(
            ['slug' => 'tokobagus'],
            [
                'user_id' => $merchant->id,
                'name' => 'TokoBagus Official Tenant',
                'plan' => 'premium',
                'status' => 'active',
            ]
        );

        // 3. Seed Top Stores
        $storesData = [
            [
                'name' => 'NovaBatik Studio',
                'slug' => 'novabatik-studio',
                'category' => 'Fashion',
                'description' => 'Batik tenun kualitas premium buatan pengrajin lokal.',
                'gmv' => 18400000,
                'total_orders' => 412,
                'rating' => 4.9,
                'badge' => 'top',
                'avatar_hue' => 220,
                'balance' => 38400000,
                'pending_escrow' => 6200000,
            ],
            [
                'name' => 'KuliKain Official',
                'slug' => 'kulikain-official',
                'category' => 'Aksesoris',
                'description' => 'Produk tas & dompet kulit asli Indonesia.',
                'gmv' => 14100000,
                'total_orders' => 318,
                'rating' => 4.8,
                'badge' => 'pro',
                'avatar_hue' => 280,
                'balance' => 18200000,
                'pending_escrow' => 3100000,
            ],
            [
                'name' => 'Jaya Elektronik',
                'slug' => 'jaya-elektronik',
                'category' => 'Elektronik',
                'description' => 'Gadget & aksesoris komputer terpercaya.',
                'gmv' => 11700000,
                'total_orders' => 287,
                'rating' => 4.7,
                'badge' => null,
                'avatar_hue' => 190,
                'balance' => 14500000,
                'pending_escrow' => 2400000,
            ],
            [
                'name' => 'Warung Digital ID',
                'slug' => 'warung-digital-id',
                'category' => 'Kuliner',
                'description' => 'Minuman & serbuk olahan kualitas terbaik.',
                'gmv' => 9200000,
                'total_orders' => 234,
                'rating' => 4.6,
                'badge' => null,
                'avatar_hue' => 150,
                'balance' => 9800000,
                'pending_escrow' => 1500000,
            ],
            [
                'name' => 'Mode Nusantara',
                'slug' => 'mode-nusantara',
                'category' => 'Sepatu',
                'description' => 'Sepatu casual dan pakaian gaya terkini.',
                'gmv' => 7800000,
                'total_orders' => 198,
                'rating' => 4.5,
                'badge' => null,
                'avatar_hue' => 30,
                'balance' => 8200000,
                'pending_escrow' => 1200000,
            ],
        ];

        $storesMap = [];

        foreach ($storesData as $s) {
            $store = Store::updateOrCreate(
                ['slug' => $s['slug']],
                array_merge($s, ['tenant_id' => $tenant->id, 'status' => 'active'])
            );
            $storesMap[$s['name']] = $store;
        }

        // 4. Seed Products
        $productsData = [
            [
                'store_name' => 'NovaBatik Studio',
                'name' => 'Kemeja Batik Tenun Premium',
                'slug' => 'kemeja-batik-tenun-premium',
                'category' => 'Fashion',
                'price' => 285000,
                'sold' => 1240,
                'rating' => 4.9,
                'tag' => 'Bestseller',
                'img' => 'https://images.unsplash.com/photo-1620799140408-edc6dcb6d633?w=400&h=400&fit=crop&auto=format',
                'stock' => 150,
            ],
            [
                'store_name' => 'Mode Nusantara',
                'name' => 'Sneakers Casual Kulit Asli',
                'slug' => 'sneakers-casual-kulit-asli',
                'category' => 'Sepatu',
                'price' => 599000,
                'sold' => 847,
                'rating' => 4.8,
                'tag' => 'Baru',
                'img' => 'https://images.unsplash.com/photo-1542291026-7eec264c27ff?w=400&h=400&fit=crop&auto=format',
                'stock' => 80,
            ],
            [
                'store_name' => 'Jaya Elektronik',
                'name' => 'Mechanical Keyboard TKL 75%',
                'slug' => 'mechanical-keyboard-tkl-75',
                'category' => 'Elektronik',
                'price' => 890000,
                'sold' => 632,
                'rating' => 4.7,
                'tag' => 'Hot',
                'img' => 'https://images.unsplash.com/photo-1618384887929-16ec33fab9ef?w=400&h=400&fit=crop&auto=format',
                'stock' => 60,
            ],
            [
                'store_name' => 'KuliKain Official',
                'name' => 'Tas Kulit Selempang Minimalis',
                'slug' => 'tas-kulit-selempang-minimalis',
                'category' => 'Aksesoris',
                'price' => 420000,
                'sold' => 921,
                'rating' => 4.8,
                'tag' => 'Bestseller',
                'img' => 'https://images.unsplash.com/photo-1548036328-c9fa89d128fa?w=400&h=400&fit=crop&auto=format',
                'stock' => 110,
            ],
            [
                'store_name' => 'Warung Digital ID',
                'name' => 'Matcha Latte Premium 200gr',
                'slug' => 'matcha-latte-premium-200gr',
                'category' => 'Kuliner',
                'price' => 145000,
                'sold' => 2103,
                'rating' => 4.9,
                'tag' => 'Hot',
                'img' => 'https://images.unsplash.com/photo-1556679343-c7306c1976bc?w=400&h=400&fit=crop&auto=format',
                'stock' => 500,
            ],
            [
                'store_name' => 'Mode Nusantara',
                'name' => 'Kacamata Frame Titanium',
                'slug' => 'kacamata-frame-titanium',
                'category' => 'Aksesoris',
                'price' => 760000,
                'sold' => 438,
                'rating' => 4.7,
                'tag' => 'Baru',
                'img' => 'https://images.unsplash.com/photo-1572635196237-14b3f281503f?w=400&h=400&fit=crop&auto=format',
                'stock' => 45,
            ],
            [
                'store_name' => 'Jaya Elektronik',
                'name' => 'Headphone Over-ear Wireless',
                'slug' => 'headphone-over-ear-wireless',
                'category' => 'Elektronik',
                'price' => 1250000,
                'sold' => 512,
                'rating' => 4.8,
                'tag' => null,
                'img' => 'https://images.unsplash.com/photo-1505740420928-5e560c06d30e?w=400&h=400&fit=crop&auto=format',
                'stock' => 35,
            ],
            [
                'store_name' => 'NovaBatik Studio',
                'name' => 'Celana Linen Wide Leg',
                'slug' => 'celana-linen-wide-leg',
                'category' => 'Fashion',
                'price' => 320000,
                'sold' => 763,
                'rating' => 4.6,
                'tag' => null,
                'img' => 'https://images.unsplash.com/photo-1594938298603-c8148c4b4b58?w=400&h=400&fit=crop&auto=format',
                'stock' => 95,
            ],
        ];

        foreach ($productsData as $pData) {
            $store = $storesMap[$pData['store_name']];
            unset($pData['store_name']);

            Product::updateOrCreate(
                ['slug' => $pData['slug']],
                array_merge($pData, ['store_id' => $store->id])
            );
        }

        // 5. Seed Orders
        $primaryStore = $storesMap['NovaBatik Studio'];

        $statuses = ['completed', 'shipped', 'packed', 'processing', 'pending', 'cancelled'];
        $customers = ['Budi Santoso', 'Siti Rahma', 'Aditya Pratama', 'Dewi Lestari', 'Rizky Febrian', 'Maya Indah'];

        foreach (range(1, 15) as $index) {
            Order::firstOrCreate(
                ['order_number' => 'ORD-2026-080'.sprintf('%02d', $index)],
                [
                    'store_id' => $primaryStore->id,
                    'customer_name' => $customers[array_rand($customers)],
                    'total_amount' => rand(150, 850) * 1000,
                    'status' => $statuses[array_rand($statuses)],
                    'created_at' => now()->subDays(rand(0, 10)),
                ]
            );
        }

        // 6. Seed Withdrawal
        Withdrawal::firstOrCreate(
            ['account_number' => '88392019481'],
            [
                'store_id' => $primaryStore->id,
                'amount' => 122700000,
                'fee' => 0,
                'bank_name' => 'BCA',
                'status' => 'transferred',
                'transferred_at' => now()->subDays(3),
            ]
        );
    }
}
