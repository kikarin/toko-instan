<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('orders')) {
            Schema::create('orders', function (Blueprint $table) {
                $table->id();
                $table->foreignId('store_id')->constrained()->cascadeOnDelete();
                $table->string('order_number')->unique();
                $table->string('customer_name');
                $table->decimal('total_amount', 15, 2);
                $table->string('status')->default('completed'); // pending, processing, packed, shipped, completed, cancelled
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('withdrawals')) {
            Schema::create('withdrawals', function (Blueprint $table) {
                $table->id();
                $table->foreignId('store_id')->constrained()->cascadeOnDelete();
                $table->decimal('amount', 15, 2);
                $table->decimal('fee', 15, 2)->default(0);
                $table->string('bank_name');
                $table->string('account_number');
                $table->string('status')->default('transferred'); // pending, approved, rejected, transferred
                $table->timestamp('transferred_at')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('withdrawals');
        Schema::dropIfExists('orders');
    }
};
