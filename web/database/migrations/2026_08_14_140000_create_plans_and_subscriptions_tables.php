<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('plans', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('name');
            $table->unsignedBigInteger('price')->default(0);
            $table->unsignedBigInteger('withdraw_fee')->default(0);
            $table->string('settlement_mode', 40)->default('escrow');
            $table->json('features')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('plan_id')->constrained('plans')->restrictOnDelete();
            $table->string('status', 40)->default('active');
            $table->timestamp('starts_at');
            $table->timestamp('ends_at')->nullable();
            $table->timestamp('renewal_notified_at')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'status']);
        });

        Schema::create('subscription_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('subscription_id')->nullable()->constrained('subscriptions')->nullOnDelete();
            $table->string('provider', 40);
            $table->unsignedBigInteger('amount');
            $table->string('status', 40)->default('pending');
            $table->string('external_id')->nullable()->index();
            $table->string('idempotency_key')->unique();
            $table->string('snap_token')->nullable();
            $table->string('redirect_url')->nullable();
            $table->json('payload')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();
        });

        $now = now();

        DB::table('plans')->insert([
            [
                'code' => 'free',
                'name' => 'Free',
                'price' => 0,
                'withdraw_fee' => 5000,
                'settlement_mode' => 'escrow',
                'features' => json_encode(['escrow' => true, 'withdraw_fee' => 5000]),
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'code' => 'premium',
                'name' => 'Premium',
                'price' => 99000,
                'withdraw_fee' => 0,
                'settlement_mode' => 'direct',
                'features' => json_encode(['direct_settlement' => true, 'withdraw_fee' => 0]),
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('subscription_payments');
        Schema::dropIfExists('subscriptions');
        Schema::dropIfExists('plans');
    }
};
