<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Schema::table('orders', function (Blueprint $table) {
        //     $table->string('payment_method', 40)->nullable()->after('status');
        //     $table->string('shipping_courier', 100)->nullable()->after('payment_method');
        //     $table->timestamp('paid_at')->nullable()->after('shipping_courier');
        // });

        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->nullable()->constrained('tenants')->nullOnDelete();
            $table->foreignId('order_id')->constrained('orders')->cascadeOnDelete();
            $table->string('provider', 40);
            $table->string('method', 40);
            $table->unsignedBigInteger('amount');
            $table->string('status', 40)->default('pending');
            $table->string('external_id')->nullable()->index();
            $table->string('idempotency_key')->unique();
            $table->string('snap_token')->nullable();
            $table->string('redirect_url')->nullable();
            $table->string('proof_path')->nullable();
            $table->string('proof_name')->nullable();
            $table->json('payload')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();

            $table->index('order_id');
            $table->index(['provider', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');

        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['payment_method', 'shipping_courier', 'paid_at']);
        });
    }
};
