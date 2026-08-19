<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('stores', function (Blueprint $table) {
            if (! Schema::hasColumn('stores', 'origin_city')) {
                $table->string('origin_city')->nullable()->after('address');
            }
            if (! Schema::hasColumn('stores', 'origin_postal_code')) {
                $table->string('origin_postal_code', 10)->nullable()->after('origin_city');
            }
        });

        Schema::table('orders', function (Blueprint $table) {
            if (! Schema::hasColumn('orders', 'shipping_cost')) {
                $table->unsignedBigInteger('shipping_cost')->default(0)->after('total_amount');
            }
            if (! Schema::hasColumn('orders', 'shipping_service')) {
                $table->string('shipping_service', 80)->nullable()->after('shipping_courier');
            }
            if (! Schema::hasColumn('orders', 'tracking_number')) {
                $table->string('tracking_number', 80)->nullable()->after('shipping_service');
            }
            if (! Schema::hasColumn('orders', 'packed_at')) {
                $table->timestamp('packed_at')->nullable()->after('paid_at');
            }
            if (! Schema::hasColumn('orders', 'shipped_at')) {
                $table->timestamp('shipped_at')->nullable()->after('packed_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('stores', function (Blueprint $table) {
            $table->dropColumn(['origin_city', 'origin_postal_code']);
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['shipping_cost', 'shipping_service', 'tracking_number', 'packed_at', 'shipped_at']);
        });
    }
};
