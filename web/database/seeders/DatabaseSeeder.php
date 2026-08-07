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
        // 1. Seed Buyer User (Niko Agustio)
        $buyer = User::firstOrCreate(
            ['email' => 'buyer@tokobagus.com'],
            [
                'name' => 'Niko Agustio',
                'username' => 'nikoagustio',
                'bio' => 'Pecinta produk lokal & teknologi',
                'phone' => '+6285264415051',
                'phone_verified_at' => now(),
                'gender' => 'Pria',
                'birth_date' => '01 January 1991',
                'password' => Hash::make('password123'),
                'role' => 'buyer',
                'auth_provider' => 'email',
                'email_verified_at' => now(),
                'avatar' => 'https://images.unsplash.com/photo-1534528741775-53994a69daeb?w=200&h=200&fit=crop&auto=format',
            ]
        );

        // 2. Seed 5 Dedicated Seller Users
        $sellersData = [
            ['email' => 'seller1@tokobagus.com', 'name' => 'Nova Batik Seller', 'username' => 'seller_novabatik'],
            ['email' => 'seller2@tokobagus.com', 'name' => 'KuliKain Seller', 'username' => 'seller_kulikain'],
            ['email' => 'seller3@tokobagus.com', 'name' => 'Jaya Elektronik Seller', 'username' => 'seller_jayaelektronik'],
            ['email' => 'seller4@tokobagus.com', 'name' => 'Warung Digital Seller', 'username' => 'seller_warungdigital'],
            ['email' => 'seller5@tokobagus.com', 'name' => 'Mode Nusantara Seller', 'username' => 'seller_modenusantara'],
        ];

        $sellersMap = [];
        foreach ($sellersData as $sData) {
            $seller = User::firstOrCreate(
                ['email' => $sData['email']],
                [
                    'name' => $sData['name'],
                    'username' => $sData['username'],
                    'password' => Hash::make('password123'),
                    'role' => 'seller',
                    'auth_provider' => 'email',
                    'email_verified_at' => now(),
                ]
            );
            $sellersMap[$sData['email']] = $seller;
        }

        // 3. Seed Admin User
        User::firstOrCreate(
            ['email' => 'admin@toko-instan.com'],
            [
                'name' => 'Admin Master',
                'username' => 'admin_master',
                'password' => Hash::make('password123'),
                'role' => 'admin',
                'auth_provider' => 'email',
                'email_verified_at' => now(),
            ]
        );

        // 4. Seed Tenant for Primary Merchant
        $tenant = Tenant::firstOrCreate(
            ['slug' => 'tokobagus'],
            [
                'user_id' => $sellersMap['seller1@tokobagus.com']->id,
                'name' => 'TokoBagus Official Tenant',
                'plan' => 'premium',
                'status' => 'active',
            ]
        );

        // 5. Seed 5 Stores linked to their Seller Accounts
        $storesData = [
            [
                'seller_email' => 'seller1@tokobagus.com',
                'name' => 'NovaBatik Studio',
                'slug' => 'novabatik-studio',
                'category' => 'Fashion',
                'description' => 'Batik tenun kualitas premium buatan pengrajin lokal Indonesia.',
                'gmv' => 18400000,
                'total_orders' => 412,
                'rating' => 4.9,
                'badge' => 'top',
                'avatar_hue' => 220,
                'balance' => 38400000,
                'pending_escrow' => 6200000,
            ],
            [
                'seller_email' => 'seller2@tokobagus.com',
                'name' => 'KuliKain Official',
                'slug' => 'kulikain-official',
                'category' => 'Aksesoris',
                'description' => 'Produk tas, dompet, dan aksesoris kulit asli buatan dalam negeri.',
                'gmv' => 14100000,
                'total_orders' => 318,
                'rating' => 4.8,
                'badge' => 'pro',
                'avatar_hue' => 280,
                'balance' => 18200000,
                'pending_escrow' => 3100000,
            ],
            [
                'seller_email' => 'seller3@tokobagus.com',
                'name' => 'Jaya Elektronik',
                'slug' => 'jaya-elektronik',
                'category' => 'Elektronik',
                'description' => 'Penyedia gadget, keyboard, headphone & aksesoris komputer terpercaya.',
                'gmv' => 11700000,
                'total_orders' => 287,
                'rating' => 4.7,
                'badge' => 'top',
                'avatar_hue' => 190,
                'balance' => 14500000,
                'pending_escrow' => 2400000,
            ],
            [
                'seller_email' => 'seller4@tokobagus.com',
                'name' => 'Warung Digital ID',
                'slug' => 'warung-digital-id',
                'category' => 'Kuliner',
                'description' => 'Aneka minuman serbuk, matcha latte, dan camilan nikmat berkualitas.',
                'gmv' => 9200000,
                'total_orders' => 234,
                'rating' => 4.6,
                'badge' => null,
                'avatar_hue' => 150,
                'balance' => 9800000,
                'pending_escrow' => 1500000,
            ],
            [
                'seller_email' => 'seller5@tokobagus.com',
                'name' => 'Mode Nusantara',
                'slug' => 'mode-nusantara',
                'category' => 'Sepatu',
                'description' => 'Sepatu casual, sneakers kulit, dan pakaian gaya modern anak muda.',
                'gmv' => 7800000,
                'total_orders' => 198,
                'rating' => 4.5,
                'badge' => 'pro',
                'avatar_hue' => 30,
                'balance' => 8200000,
                'pending_escrow' => 1200000,
            ],
        ];

        $storesMap = [];

        foreach ($storesData as $s) {
            $sellerEmail = $s['seller_email'];
            unset($s['seller_email']);

            $store = Store::updateOrCreate(
                ['slug' => $s['slug']],
                array_merge($s, ['tenant_id' => $tenant->id, 'status' => 'active'])
            );
            $storesMap[$s['name']] = $store;
        }

        // 6. Seed 25+ Comprehensive Products
        $productsData = [
            // NovaBatik Studio (Fashion)
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
                'store_name' => 'NovaBatik Studio',
                'name' => 'Celana Linen Wide Leg',
                'slug' => 'celana-linen-wide-leg',
                'category' => 'Fashion',
                'price' => 320000,
                'sold' => 763,
                'rating' => 4.6,
                'tag' => 'Baru',
                'img' => 'https://images.unsplash.com/photo-1594938298603-c8148c4b4b58?w=400&h=400&fit=crop&auto=format',
                'stock' => 95,
            ],
            [
                'store_name' => 'NovaBatik Studio',
                'name' => 'Gaun Batik Modern Elegan',
                'slug' => 'gaun-batik-modern-elegan',
                'category' => 'Fashion',
                'price' => 450000,
                'sold' => 512,
                'rating' => 4.8,
                'tag' => 'Hot',
                'img' => 'https://images.unsplash.com/photo-1515886657613-9f3515b0c78f?w=400&h=400&fit=crop&auto=format',
                'stock' => 60,
            ],
            [
                'store_name' => 'NovaBatik Studio',
                'name' => 'Outer Outerwear Motif Parang',
                'slug' => 'outer-outerwear-motif-parang',
                'category' => 'Fashion',
                'price' => 275000,
                'sold' => 340,
                'rating' => 4.7,
                'tag' => null,
                'img' => 'https://images.unsplash.com/photo-1539109136881-3be0616acf4b?w=400&h=400&fit=crop&auto=format',
                'stock' => 80,
            ],
            [
                'store_name' => 'NovaBatik Studio',
                'name' => 'Jaket Denim Kombinasi Batik',
                'slug' => 'jaket-denim-kombinasi-batik',
                'category' => 'Fashion',
                'price' => 495000,
                'sold' => 290,
                'rating' => 4.8,
                'tag' => 'Bestseller',
                'img' => 'https://images.unsplash.com/photo-1543076447-215ad9ba6923?w=400&h=400&fit=crop&auto=format',
                'stock' => 40,
            ],

            // KuliKain Official (Aksesoris)
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
                'store_name' => 'KuliKain Official',
                'name' => 'Dompet Kulit Asli Slot Kartu',
                'slug' => 'dompet-kulit-asli-slot-kartu',
                'category' => 'Aksesoris',
                'price' => 185000,
                'sold' => 1430,
                'rating' => 4.9,
                'tag' => 'Hot',
                'img' => 'https://images.unsplash.com/photo-1627123424574-724758594e93?w=400&h=400&fit=crop&auto=format',
                'stock' => 200,
            ],
            [
                'store_name' => 'KuliKain Official',
                'name' => 'Ikat Pinggang Kulit Sapi Premium',
                'slug' => 'ikat-pinggang-kulit-sapi-premium',
                'category' => 'Aksesoris',
                'price' => 210000,
                'sold' => 670,
                'rating' => 4.7,
                'tag' => null,
                'img' => 'https://images.unsplash.com/photo-1553062407-98eeb64c6a62?w=400&h=400&fit=crop&auto=format',
                'stock' => 120,
            ],
            [
                'store_name' => 'KuliKain Official',
                'name' => 'Ransel Canvas Kulit Vintage',
                'slug' => 'ransel-canvas-kulit-vintage',
                'category' => 'Aksesoris',
                'price' => 580000,
                'sold' => 410,
                'rating' => 4.8,
                'tag' => 'Baru',
                'img' => 'https://images.unsplash.com/photo-1553062407-98eeb64c6a62?w=400&h=400&fit=crop&auto=format',
                'stock' => 50,
            ],

            // Jaya Elektronik (Elektronik)
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
                'store_name' => 'Jaya Elektronik',
                'name' => 'Headphone Over-ear Wireless',
                'slug' => 'headphone-over-ear-wireless',
                'category' => 'Elektronik',
                'price' => 1250000,
                'sold' => 512,
                'rating' => 4.8,
                'tag' => 'Bestseller',
                'img' => 'https://images.unsplash.com/photo-1505740420928-5e560c06d30e?w=400&h=400&fit=crop&auto=format',
                'stock' => 35,
            ],
            [
                'store_name' => 'Jaya Elektronik',
                'name' => 'Mouse Gaming Ergonomis RGB',
                'slug' => 'mouse-gaming-ergonomis-rgb',
                'category' => 'Elektronik',
                'price' => 345000,
                'sold' => 890,
                'rating' => 4.6,
                'tag' => 'Baru',
                'img' => 'https://images.unsplash.com/photo-1527864550417-7fd91fc51a46?w=400&h=400&fit=crop&auto=format',
                'stock' => 140,
            ],
            [
                'store_name' => 'Jaya Elektronik',
                'name' => 'Smartwatch Sport GPS Monitor',
                'slug' => 'smartwatch-sport-gps-monitor',
                'category' => 'Elektronik',
                'price' => 1100000,
                'sold' => 380,
                'rating' => 4.7,
                'tag' => 'Hot',
                'img' => 'https://images.unsplash.com/photo-1523275335684-37898b6baf30?w=400&h=400&fit=crop&auto=format',
                'stock' => 45,
            ],
            [
                'store_name' => 'Jaya Elektronik',
                'name' => 'Speaker Bluetooth Portable Bass',
                'slug' => 'speaker-bluetooth-portable-bass',
                'category' => 'Elektronik',
                'price' => 475000,
                'sold' => 720,
                'rating' => 4.8,
                'tag' => 'Bestseller',
                'img' => 'https://images.unsplash.com/photo-1608043152269-423dbba4e7e1?w=400&h=400&fit=crop&auto=format',
                'stock' => 90,
            ],

            // Warung Digital ID (Kuliner)
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
                'store_name' => 'Warung Digital ID',
                'name' => 'Biji Kopi Arabika Gayo 250g',
                'slug' => 'biji-kopi-arabika-gayo-250g',
                'category' => 'Kuliner',
                'price' => 98000,
                'sold' => 1650,
                'rating' => 4.9,
                'tag' => 'Bestseller',
                'img' => 'https://images.unsplash.com/photo-1559056199-641a0ac8b55e?w=400&h=400&fit=crop&auto=format',
                'stock' => 300,
            ],
            [
                'store_name' => 'Warung Digital ID',
                'name' => 'Teh Herbal Chamomile Organic',
                'slug' => 'teh-herbal-chamomile-organic',
                'category' => 'Kuliner',
                'price' => 75000,
                'sold' => 840,
                'rating' => 4.7,
                'tag' => 'Baru',
                'img' => 'https://images.unsplash.com/photo-1576092768241-dec231879fc3?w=400&h=400&fit=crop&auto=format',
                'stock' => 220,
            ],
            [
                'store_name' => 'Warung Digital ID',
                'name' => 'Cokelat Artisan Dark 70%',
                'slug' => 'cokelat-artisan-dark-70',
                'category' => 'Kuliner',
                'price' => 65000,
                'sold' => 1120,
                'rating' => 4.8,
                'tag' => null,
                'img' => 'https://images.unsplash.com/photo-1549007994-cb92caebd54b?w=400&h=400&fit=crop&auto=format',
                'stock' => 180,
            ],

            // Mode Nusantara (Sepatu & Aksesoris)
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
                'store_name' => 'Mode Nusantara',
                'name' => 'Sepatu Loafers Suede Brown',
                'slug' => 'sepatu-loafers-suede-brown',
                'category' => 'Sepatu',
                'price' => 485000,
                'sold' => 560,
                'rating' => 4.7,
                'tag' => 'Hot',
                'img' => 'https://images.unsplash.com/photo-1533867617858-e7b97e060509?w=400&h=400&fit=crop&auto=format',
                'stock' => 70,
            ],
            [
                'store_name' => 'Mode Nusantara',
                'name' => 'Sandal Slip-on Minimalis Leather',
                'slug' => 'sandal-slip-on-minimalis-leather',
                'category' => 'Sepatu',
                'price' => 230000,
                'sold' => 940,
                'rating' => 4.6,
                'tag' => 'Bestseller',
                'img' => 'https://images.unsplash.com/photo-1603808033192-082d6919d3e1?w=400&h=400&fit=crop&auto=format',
                'stock' => 160,
            ],
            [
                'store_name' => 'Mode Nusantara',
                'name' => 'Sepatu Boots Kulit High Top',
                'slug' => 'sepatu-boots-kulit-high-top',
                'category' => 'Sepatu',
                'price' => 780000,
                'sold' => 310,
                'rating' => 4.9,
                'tag' => 'Baru',
                'img' => 'https://images.unsplash.com/photo-1520639888713-7851133b1ed0?w=400&h=400&fit=crop&auto=format',
                'stock' => 35,
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

        // 7. Seed Orders for Buyer (Niko Agustio)
        $primaryStore = $storesMap['NovaBatik Studio'];

        $sampleOrders = [
            ['order_number' => 'ORD-2026-08001', 'status' => 'pending', 'amount' => 285000],
            ['order_number' => 'ORD-2026-08002', 'status' => 'paid', 'amount' => 599000],
            ['order_number' => 'ORD-2026-08003', 'status' => 'shipped', 'amount' => 420000],
            ['order_number' => 'ORD-2026-08004', 'status' => 'completed', 'amount' => 145000],
            ['order_number' => 'ORD-2026-08005', 'status' => 'completed', 'amount' => 890000],
        ];

        foreach ($sampleOrders as $ord) {
            Order::firstOrCreate(
                ['order_number' => $ord['order_number']],
                [
                    'store_id' => $primaryStore->id,
                    'customer_name' => $buyer->name,
                    'customer_email' => $buyer->email,
                    'customer_phone' => $buyer->phone,
                    'shipping_address' => 'Jl. Jendral Sudirman No. 42, Jakarta Selatan, DKI Jakarta',
                    'total_amount' => $ord['amount'],
                    'status' => $ord['status'],
                    'created_at' => now()->subDays(rand(1, 5)),
                ]
            );
        }

        // 8. Seed Withdrawal
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
