<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('wallet_transactions')) {
            Schema::create('wallet_transactions', function (Blueprint $table) {
                $table->id();
                $table->foreignId('store_id')->constrained()->cascadeOnDelete();
                $table->string('type'); // credit, debit
                $table->decimal('amount', 15, 2);
                $table->decimal('fee', 15, 2)->default(0);
                $table->decimal('net_amount', 15, 2);
                $table->string('reference_type')->nullable(); // order, withdraw, refund
                $table->string('reference_id')->nullable();
                $table->text('description')->nullable();
                $table->string('status')->default('completed');
                $table->timestamps();
            });
        }

        Schema::table('withdrawals', function (Blueprint $table) {
            if (! Schema::hasColumn('withdrawals', 'account_name')) {
                $table->string('account_name')->nullable();
                $table->text('notes')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wallet_transactions');
        Schema::table('withdrawals', function (Blueprint $table) {
            $table->dropColumn(['account_name', 'notes']);
        });
    }
};
