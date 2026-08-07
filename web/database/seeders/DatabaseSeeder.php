<?php

namespace Database\Seeders;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Label;
use App\Models\Order;
use App\Models\Product;
use App\Models\Store;
use App\Models\Tenant;
use App\Models\User;
use App\Models\Wallet;
use App\Models\Withdrawal;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

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
                'bio' => 'Pecinta produk Nike & sneakers original',
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

        // 2. Seed Nike Store Seller User
        $seller = User::firstOrCreate(
            ['email' => 'seller@nike.com'],
            [
                'name' => 'Nike Official Manager',
                'username' => 'nike_official',
                'password' => Hash::make('password123'),
                'role' => 'seller',
                'auth_provider' => 'email',
                'email_verified_at' => now(),
                'avatar' => 'https://images.unsplash.com/photo-1542291026-7eec264c27ff?w=200&h=200&fit=crop&auto=format',
            ]
        );

        // 3. Seed Admin User
        User::firstOrCreate(
            ['email' => 'admin@nike.com'],
            [
                'name' => 'Nike Admin Master',
                'username' => 'admin_nike',
                'password' => Hash::make('password123'),
                'role' => 'admin',
                'auth_provider' => 'email',
                'email_verified_at' => now(),
            ]
        );

        // 4. Seed Tenant
        $tenant = Tenant::firstOrCreate(
            ['slug' => 'nike-indonesia'],
            [
                'user_id' => $seller->id,
                'name' => 'Nike Indonesia Official Tenant',
                'plan' => 'enterprise',
                'status' => 'active',
            ]
        );

        // Seed Categories, Labels, and Brands for Tenant
        foreach (['Sneakers', 'Apparel', 'Accessories', 'Sportswear', 'Running'] as $catName) {
            Category::firstOrCreate(
                ['tenant_id' => $tenant->id, 'name' => $catName],
                ['slug' => Str::slug($catName)]
            );
        }

        foreach (['BESTSELLER', 'NEW ARRIVAL', 'PROMO 8.8', 'GARANSI RESMI', 'LIMITED EDITION'] as $lbl) {
            Label::firstOrCreate(
                ['tenant_id' => $tenant->id, 'name' => $lbl],
                ['slug' => Str::slug($lbl), 'color' => '#f59e0b']
            );
        }

        foreach (['Nike', 'Jordan', 'Adidas', 'Puma', 'Converse'] as $bnd) {
            Brand::firstOrCreate(
                ['tenant_id' => $tenant->id, 'name' => $bnd],
                ['slug' => Str::slug($bnd)]
            );
        }

        // 5. Seed Nike Official Store
        $nikeStore = Store::updateOrCreate(
            ['slug' => 'nike-official'],
            [
                'tenant_id' => $tenant->id,
                'name' => 'Nike Official Store',
                'category' => 'Sportswear',
                'description' => 'Toko Resmi Nike Indonesia. Garansi 100% Produk Asli & Original.',
                'gmv' => 184500000,
                'total_orders' => 2480,
                'rating' => 4.9,
                'badge' => 'top',
                'avatar_hue' => 25,
                'balance' => 95400000,
                'pending_escrow' => 12500000,
                'status' => 'active',
            ]
        );

        // 6. Seed Wallet & opening ledger (ke sini Dashboard/seller baca saldo)
        $wallet = Wallet::firstOrCreate(
            ['tenant_id' => $tenant->id],
            [
                'balance' => 95400000,
                'pending_balance' => 12500000,
                'currency' => 'IDR',
            ]
        );

        if ($wallet->transactions()->doesntExist()) {
            $wallet->transactions()->create([
                'tenant_id' => $tenant->id,
                'type' => 'adjustment',
                'direction' => 'credit',
                'amount' => 95400000,
                'balance_after' => 95400000,
                'pending_after' => 12500000,
                'reference_type' => null,
                'reference_id' => null,
                'description' => 'Saldo awal (seed)',
                'created_at' => now(),
            ]);
        }

        // 7. Seed 25+ Nike Official Products
        $nikeProducts = [
            // Sneakers & Footwear
            [
                'name' => "Nike Air Force 1 '07",
                'slug' => 'nike-air-force-1-07',
                'category' => 'Sneakers',
                'price' => 1549000,
                'sold' => 3420,
                'rating' => 4.9,
                'tag' => 'Bestseller',
                'img' => 'https://images.unsplash.com/photo-1595950653106-6c9ebd614d3a?w=600&h=600&fit=crop&auto=format',
                'stock' => 120,
            ],
            [
                'name' => 'Nike Air Max 270 Black Red',
                'slug' => 'nike-air-max-270-black-red',
                'category' => 'Sneakers',
                'price' => 2299000,
                'sold' => 1840,
                'rating' => 4.8,
                'tag' => 'Hot',
                'img' => 'https://images.unsplash.com/photo-1542291026-7eec264c27ff?w=600&h=600&fit=crop&auto=format',
                'stock' => 85,
            ],
            [
                'name' => 'Nike Dunk Low Retro Panda',
                'slug' => 'nike-dunk-low-retro-panda',
                'category' => 'Sneakers',
                'price' => 1999000,
                'sold' => 2950,
                'rating' => 4.9,
                'tag' => 'Bestseller',
                'img' => 'https://images.unsplash.com/photo-1600185365926-3a2ce3cdb9eb?w=600&h=600&fit=crop&auto=format',
                'stock' => 50,
            ],
            [
                'name' => "Nike Blazer Mid '77 Vintage",
                'slug' => 'nike-blazer-mid-77-vintage',
                'category' => 'Sneakers',
                'price' => 1499000,
                'sold' => 980,
                'rating' => 4.7,
                'tag' => 'Baru',
                'img' => 'https://images.unsplash.com/photo-1515955656352-a1fa3ffcd111?w=600&h=600&fit=crop&auto=format',
                'stock' => 70,
            ],
            [
                'name' => 'Nike Court Vision Low White',
                'slug' => 'nike-court-vision-low-white',
                'category' => 'Sneakers',
                'price' => 999000,
                'sold' => 1420,
                'rating' => 4.6,
                'tag' => null,
                'img' => 'https://images.unsplash.com/photo-1584735935682-2f2b69dff9d2?w=600&h=600&fit=crop&auto=format',
                'stock' => 110,
            ],
            [
                'name' => 'Nike Air Jordan 1 Low Shadow',
                'slug' => 'nike-air-jordan-1-low-shadow',
                'category' => 'Sneakers',
                'price' => 2499000,
                'sold' => 890,
                'rating' => 4.9,
                'tag' => 'Hot',
                'img' => 'https://images.unsplash.com/photo-1552346154-21d32810aba3?w=600&h=600&fit=crop&auto=format',
                'stock' => 40,
            ],

            // Running Shoes
            [
                'name' => 'Nike Pegasus 40 Road Running',
                'slug' => 'nike-pegasus-40-road-running',
                'category' => 'Running',
                'price' => 2099000,
                'sold' => 1250,
                'rating' => 4.9,
                'tag' => 'Bestseller',
                'img' => 'https://images.unsplash.com/photo-1606107557195-0e29a4b5b4aa?w=600&h=600&fit=crop&auto=format',
                'stock' => 90,
            ],
            [
                'name' => 'Nike Invincible 3 Cushion',
                'slug' => 'nike-invincible-3-cushion',
                'category' => 'Running',
                'price' => 2899000,
                'sold' => 640,
                'rating' => 4.8,
                'tag' => 'Hot',
                'img' => 'https://images.unsplash.com/photo-1514989940723-e8e51635b782?w=600&h=600&fit=crop&auto=format',
                'stock' => 45,
            ],
            [
                'name' => 'Nike Revolution 6 Next Nature',
                'slug' => 'nike-revolution-6-next-nature',
                'category' => 'Running',
                'price' => 849000,
                'sold' => 2100,
                'rating' => 4.6,
                'tag' => null,
                'img' => 'https://images.unsplash.com/photo-1582588678413-dbf45f4823e9?w=600&h=600&fit=crop&auto=format',
                'stock' => 150,
            ],
            [
                'name' => 'Nike InfinityRN 4 Gore-Tex',
                'slug' => 'nike-infinityrn-4-gore-tex',
                'category' => 'Running',
                'price' => 2699000,
                'sold' => 430,
                'rating' => 4.8,
                'tag' => 'Baru',
                'img' => 'https://images.unsplash.com/photo-1511556532299-8f662fc26c06?w=600&h=600&fit=crop&auto=format',
                'stock' => 55,
            ],

            // Apparel & Sportswear
            [
                'name' => 'Nike Tech Fleece Full-Zip Hoodie',
                'slug' => 'nike-tech-fleece-full-zip-hoodie',
                'category' => 'Apparel',
                'price' => 1899000,
                'sold' => 1560,
                'rating' => 4.9,
                'tag' => 'Bestseller',
                'img' => 'https://images.unsplash.com/photo-1556905055-8f358a7a47b2?w=600&h=600&fit=crop&auto=format',
                'stock' => 75,
            ],
            [
                'name' => 'Nike Dri-FIT Hyverse Short-Sleeve',
                'slug' => 'nike-dri-fit-hyverse-short-sleeve',
                'category' => 'Apparel',
                'price' => 499000,
                'sold' => 2300,
                'rating' => 4.8,
                'tag' => 'Hot',
                'img' => 'https://images.unsplash.com/photo-1521572267360-ee0c2909d518?w=600&h=600&fit=crop&auto=format',
                'stock' => 200,
            ],
            [
                'name' => 'Nike Club Fleece Jogger Pants',
                'slug' => 'nike-club-fleece-jogger-pants',
                'category' => 'Apparel',
                'price' => 849000,
                'sold' => 1140,
                'rating' => 4.7,
                'tag' => null,
                'img' => 'https://images.unsplash.com/photo-1517445312882-bc9910d016b7?w=600&h=600&fit=crop&auto=format',
                'stock' => 95,
            ],
            [
                'name' => 'Nike Sportswear Essential Tee',
                'slug' => 'nike-sportswear-essential-tee',
                'category' => 'Apparel',
                'price' => 399000,
                'sold' => 1890,
                'rating' => 4.7,
                'tag' => null,
                'img' => 'https://images.unsplash.com/photo-1503342217505-b0a15ec3261c?w=600&h=600&fit=crop&auto=format',
                'stock' => 160,
            ],
            [
                'name' => 'Nike Pro Warm Top Long Sleeve',
                'slug' => 'nike-pro-warm-top-long-sleeve',
                'category' => 'Apparel',
                'price' => 699000,
                'sold' => 620,
                'rating' => 4.8,
                'tag' => 'Baru',
                'img' => 'https://images.unsplash.com/photo-1518459031867-a89b944bffe4?w=600&h=600&fit=crop&auto=format',
                'stock' => 80,
            ],

            // Basketball
            [
                'name' => 'Nike LeBron XXI Basketball',
                'slug' => 'nike-lebron-xxi-basketball',
                'category' => 'Basketball',
                'price' => 3099000,
                'sold' => 780,
                'rating' => 4.9,
                'tag' => 'Hot',
                'img' => 'https://images.unsplash.com/photo-1579338559194-a162d19bf842?w=600&h=600&fit=crop&auto=format',
                'stock' => 35,
            ],
            [
                'name' => 'Nike G.T. Cut 3 EP',
                'slug' => 'nike-g-t-cut-3-ep',
                'category' => 'Basketball',
                'price' => 2849000,
                'sold' => 520,
                'rating' => 4.8,
                'tag' => 'Baru',
                'img' => 'https://images.unsplash.com/photo-1543508282-6319a3e2621f?w=600&h=600&fit=crop&auto=format',
                'stock' => 40,
            ],

            // Accessories
            [
                'name' => 'Nike Heritage Backpack 25L',
                'slug' => 'nike-heritage-backpack-25l',
                'category' => 'Accessories',
                'price' => 549000,
                'sold' => 2410,
                'rating' => 4.8,
                'tag' => 'Bestseller',
                'img' => 'https://images.unsplash.com/photo-1553062407-98eeb64c6a62?w=600&h=600&fit=crop&auto=format',
                'stock' => 130,
            ],
            [
                'name' => 'Nike Everyday Socks (3 Pairs)',
                'slug' => 'nike-everyday-socks-3-pairs',
                'category' => 'Accessories',
                'price' => 229000,
                'sold' => 4500,
                'rating' => 4.9,
                'tag' => 'Bestseller',
                'img' => 'https://images.unsplash.com/photo-1586350977771-b3b0abd50c82?w=600&h=600&fit=crop&auto=format',
                'stock' => 300,
            ],
            [
                'name' => 'Nike Featherlight Adjustable Cap',
                'slug' => 'nike-featherlight-adjustable-cap',
                'category' => 'Accessories',
                'price' => 349000,
                'sold' => 1320,
                'rating' => 4.7,
                'tag' => null,
                'img' => 'https://images.unsplash.com/photo-1588850561407-ed78c282e89b?w=600&h=600&fit=crop&auto=format',
                'stock' => 140,
            ],
            [
                'name' => 'Nike Brasilia Small Duffel Bag',
                'slug' => 'nike-brasilia-small-duffel-bag',
                'category' => 'Accessories',
                'price' => 499000,
                'sold' => 970,
                'rating' => 4.8,
                'tag' => 'Hot',
                'img' => 'https://images.unsplash.com/photo-1544816155-12df9643f363?w=600&h=600&fit=crop&auto=format',
                'stock' => 85,
            ],
            [
                'name' => 'Nike Swoosh Wristbands (Pair)',
                'slug' => 'nike-swoosh-wristbands-pair',
                'category' => 'Accessories',
                'price' => 129000,
                'sold' => 1890,
                'rating' => 4.7,
                'tag' => null,
                'img' => 'https://images.unsplash.com/photo-1576243345690-4e4b79b63288?w=600&h=600&fit=crop&auto=format',
                'stock' => 250,
            ],
        ];

        foreach ($nikeProducts as $pData) {
            Product::updateOrCreate(
                ['slug' => $pData['slug']],
                array_merge($pData, ['store_id' => $nikeStore->id, 'is_active' => true])
            );
        }

        // 8. Seed Sample Orders for Buyer (Niko Agustio)
        $sampleOrders = [
            ['order_number' => 'ORD-NIKE-001', 'status' => 'pending', 'amount' => 1549000],
            ['order_number' => 'ORD-NIKE-002', 'status' => 'paid', 'amount' => 2299000],
            ['order_number' => 'ORD-NIKE-003', 'status' => 'shipped', 'amount' => 1899000],
            ['order_number' => 'ORD-NIKE-004', 'status' => 'completed', 'amount' => 549000],
            ['order_number' => 'ORD-NIKE-005', 'status' => 'completed', 'amount' => 2099000],
        ];

        foreach ($sampleOrders as $ord) {
            Order::firstOrCreate(
                ['order_number' => $ord['order_number']],
                [
                    'store_id' => $nikeStore->id,
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

        // 9. Seed Withdrawal
        Withdrawal::updateOrCreate(
            ['account_number' => '88392019481'],
            [
                'store_id' => $nikeStore->id,
                'tenant_id' => $tenant->id,
                'wallet_id' => $wallet->id,
                'amount' => 95400000,
                'fee' => 0,
                'net_amount' => 95400000,
                'bank_name' => 'BCA',
                'status' => 'transferred',
                'transferred_at' => now()->subDays(3),
            ]
        );
    }
}
