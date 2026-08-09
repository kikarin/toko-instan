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
use Illuminate\Support\Str;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedNike();
        $this->seedAdidas();
    }

    private function seedNike(): void
    {
        $seller = User::where('email', 'seller.nike@gmail.com')->first();
        if (!$seller) return;

        $tenant = Tenant::firstOrCreate(
            ['slug' => 'nike-indonesia-tenant'],
            [
                'user_id' => $seller->id,
                'name' => 'Nike Indonesia Official Tenant',
                'plan' => 'enterprise',
                'status' => 'active',
            ]
        );

        $store = Store::firstOrCreate(
            ['slug' => 'nike-indonesia'],
            [
                'tenant_id' => $tenant->id,
                'name' => 'Nike Indonesia',
                'category' => 'Sportswear',
                'description' => 'Toko Resmi Nike Indonesia.',
                'gmv' => 184500000,
                'total_orders' => 2480,
                'rating' => 4.9,
                'badge' => 'top',
                'status' => 'active',
            ]
        );

        // Assign Buyer A to this Store
        $buyer = User::where('email', 'buyer@gmail.com')->first();
        if ($buyer) {
            $buyer->update(['store_id' => $store->id]);
        }

        $wallet = Wallet::firstOrCreate(
            ['tenant_id' => $tenant->id],
            ['balance' => 95400000, 'pending_balance' => 12500000, 'currency' => 'IDR']
        );

        $products = [
            [
                'name' => "Nike Air Force 1 '07",
                'slug' => 'nike-air-force-1-07',
                'category' => 'Sneakers',
                'price' => 1549000,
                'stock' => 120,
                'img' => 'https://images.unsplash.com/photo-1595950653106-6c9ebd614d3a?w=600&h=600&fit=crop&auto=format',
            ],
            [
                'name' => 'Nike Air Max 270',
                'slug' => 'nike-air-max-270',
                'category' => 'Sneakers',
                'price' => 2299000,
                'stock' => 85,
                'img' => 'https://images.unsplash.com/photo-1542291026-7eec264c27ff?w=600&h=600&fit=crop&auto=format',
            ]
        ];

        foreach ($products as $pData) {
            Product::updateOrCreate(
                ['slug' => $pData['slug']],
                array_merge($pData, ['store_id' => $store->id, 'is_active' => true])
            );
        }
    }

    private function seedAdidas(): void
    {
        $seller = User::where('email', 'seller.adidas@gmail.com')->first();
        if (!$seller) return;

        $tenant = Tenant::firstOrCreate(
            ['slug' => 'adidas-indonesia-tenant'],
            [
                'user_id' => $seller->id,
                'name' => 'Adidas Indonesia Official Tenant',
                'plan' => 'enterprise',
                'status' => 'active',
            ]
        );

        $store = Store::updateOrCreate(
            ['slug' => 'adidas-indonesia'],
            [
                'tenant_id' => $tenant->id,
                'name' => 'Adidas Indonesia',
                'category' => 'Sportswear',
                'description' => 'Toko Resmi Adidas Indonesia.',
                'gmv' => 104500000,
                'total_orders' => 1480,
                'rating' => 4.8,
                'badge' => 'top',
                'status' => 'active',
                'banner_url' => 'https://images.unsplash.com/photo-1555274175-6cbf6f3b137b?w=1200&h=400&fit=crop&auto=format',
                'banner_urls' => [
                    'https://images.unsplash.com/photo-1555274175-6cbf6f3b137b?w=1200&h=400&fit=crop&auto=format',
                    'https://images.unsplash.com/photo-1518002171953-a080ee817e1f?w=1200&h=400&fit=crop&auto=format',
                    'https://images.unsplash.com/photo-1542291026-7eec264c27ff?w=1200&h=400&fit=crop&auto=format'
                ],
            ]
        );

        // Assign Buyer B to this Store
        $buyer = User::where('email', 'buyer2@gmail.com')->first();
        if ($buyer) {
            $buyer->update(['store_id' => $store->id]);
        }

        $wallet = Wallet::firstOrCreate(
            ['tenant_id' => $tenant->id],
            ['balance' => 55400000, 'pending_balance' => 2500000, 'currency' => 'IDR']
        );

        $products = [
            [
                'name' => "Adidas Ultraboost 22",
                'slug' => 'adidas-ultraboost-22',
                'category' => 'Sneakers',
                'price' => 3300000,
                'stock' => 50,
                'img' => 'https://images.unsplash.com/photo-1587563871167-1ee9c731aefb?w=600&h=600&fit=crop&auto=format',
            ],
            [
                'name' => 'Adidas NMD R1',
                'slug' => 'adidas-nmd-r1',
                'category' => 'Sneakers',
                'price' => 2200000,
                'stock' => 45,
                'img' => 's',
            ]
        ];

        foreach ($products as $pData) {
            Product::updateOrCreate(
                ['slug' => $pData['slug']],
                array_merge($pData, ['store_id' => $store->id, 'is_active' => true])
            );
        }
    }
}
