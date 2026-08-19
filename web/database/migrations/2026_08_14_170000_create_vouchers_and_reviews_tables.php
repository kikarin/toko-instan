<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vouchers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('store_id')->constrained()->cascadeOnDelete();
            $table->string('code', 40);
            $table->string('name');
            $table->string('type', 20);
            $table->unsignedBigInteger('value');
            $table->unsignedBigInteger('min_spend')->default(0);
            $table->unsignedBigInteger('max_discount')->nullable();
            $table->unsignedInteger('usage_limit')->nullable();
            $table->unsignedInteger('used_count')->default(0);
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['store_id', 'code']);
        });

        Schema::create('reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('store_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('order_item_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('rating');
            $table->text('body')->nullable();
            $table->string('photo_path')->nullable();
            $table->timestamps();

            $table->unique('order_item_id');
        });

        Schema::table('orders', function (Blueprint $table) {
            if (! Schema::hasColumn('orders', 'discount')) {
                $table->unsignedBigInteger('discount')->default(0)->after('shipping_cost');
            }
            if (! Schema::hasColumn('orders', 'tax')) {
                $table->unsignedBigInteger('tax')->default(0)->after('discount');
            }
            if (! Schema::hasColumn('orders', 'voucher_id')) {
                $table->foreignId('voucher_id')->nullable()->after('tax')->constrained()->nullOnDelete();
            }
            if (! Schema::hasColumn('orders', 'voucher_code')) {
                $table->string('voucher_code', 40)->nullable()->after('voucher_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            if (Schema::hasColumn('orders', 'voucher_id')) {
                $table->dropConstrainedForeignId('voucher_id');
            }
            $table->dropColumn(array_filter([
                Schema::hasColumn('orders', 'discount') ? 'discount' : null,
                Schema::hasColumn('orders', 'tax') ? 'tax' : null,
                Schema::hasColumn('orders', 'voucher_code') ? 'voucher_code' : null,
            ]));
        });

        Schema::dropIfExists('reviews');
        Schema::dropIfExists('vouchers');
    }
};
