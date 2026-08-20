<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->uuid('customer_id')->nullable()->after('store_id');
            $table->string('shipping_courier')->nullable()->after('shipping_address');
            $table->string('payment_method')->nullable()->after('shipping_courier');
            $table->timestamp('paid_at')->nullable()->after('status');
            $table->string('tracking_number')->nullable()->after('paid_at');
            $table->string('tracking_courier')->nullable()->after('tracking_number');
            $table->timestamp('shipped_at')->nullable()->after('tracking_courier');

            $table->foreign('customer_id')->references('id')->on('customers')->nullOnDelete();
            $table->index('customer_id');
        });

        $this->backfillCustomersFromOrders();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropForeign(['customer_id']);
            $table->dropIndex(['customer_id']);
            $table->dropColumn([
                'customer_id',
                'shipping_courier',
                'payment_method',
                'paid_at',
                'tracking_number',
                'tracking_courier',
                'shipped_at',
            ]);
        });
    }

    /**
     * Seed customers from existing orders so the seller CRM is complete
     * for historical data.
     */
    private function backfillCustomersFromOrders(): void
    {
        $rows = DB::table('orders')
            ->select('store_id', 'customer_email')
            ->selectRaw('MIN(customer_name) as customer_name')
            ->selectRaw('MIN(customer_phone) as customer_phone')
            ->whereNotNull('customer_email')
            ->where('customer_email', '!=', '')
            ->groupBy('store_id', 'customer_email')
            ->get();

        foreach ($rows as $row) {
            $exists = DB::table('customers')
                ->where('store_id', $row->store_id)
                ->where('email', $row->customer_email)
                ->exists();

            if ($exists) {
                continue;
            }

            $customerId = (string) Str::uuid();

            DB::table('customers')->insert([
                'id' => $customerId,
                'store_id' => $row->store_id,
                'user_id' => null,
                'name' => $row->customer_name ?? 'Tanpa Nama',
                'email' => $row->customer_email,
                'phone' => $row->customer_phone,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::table('orders')
                ->where('store_id', $row->store_id)
                ->where('customer_email', $row->customer_email)
                ->update(['customer_id' => $customerId]);
        }
    }
};
