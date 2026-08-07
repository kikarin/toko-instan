<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('wallets')) {
            Schema::create('wallets', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tenant_id')->unique()->constrained()->cascadeOnDelete();
                $table->decimal('balance', 15, 2)->default(0);
                $table->decimal('pending_balance', 15, 2)->default(0);
                $table->string('currency')->default('IDR');
                $table->timestamps();
            });
        }

        if (Schema::hasTable('wallet_transactions')) {
            Schema::dropIfExists('wallet_transactions');
        }

        Schema::create('wallet_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('wallet_id')->constrained()->cascadeOnDelete();
            $table->string('type');
            $table->string('direction');
            $table->decimal('amount', 15, 2);
            $table->decimal('balance_after', 15, 2);
            $table->decimal('pending_after', 15, 2);
            $table->string('reference_type')->nullable();
            $table->string('reference_id')->nullable();
            $table->string('description')->nullable();
            $table->timestamp('created_at');

            $table->index(['wallet_id', 'created_at']);
            $table->index('tenant_id');
            $table->index(['reference_type', 'reference_id']);
        });

        Schema::table('withdrawals', function (Blueprint $table) {
            if (! Schema::hasColumn('withdrawals', 'wallet_id')) {
                $table->foreignId('wallet_id')->nullable()->after('store_id');
            }
            if (! Schema::hasColumn('withdrawals', 'tenant_id')) {
                $table->foreignId('tenant_id')->nullable()->after('store_id');
            }
            if (! Schema::hasColumn('withdrawals', 'net_amount')) {
                $table->decimal('net_amount', 15, 2)->default(0)->after('fee');
            }
            if (! Schema::hasColumn('withdrawals', 'approved_at')) {
                $table->timestamp('approved_at')->nullable()->after('status');
            }
            if (! Schema::hasColumn('withdrawals', 'rejected_reason')) {
                $table->text('rejected_reason')->nullable()->after('approved_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('withdrawals', function (Blueprint $table) {
            $table->dropColumn(['wallet_id', 'tenant_id', 'net_amount', 'approved_at', 'rejected_reason']);
        });

        Schema::dropIfExists('wallet_transactions');
        Schema::dropIfExists('wallets');
    }
};
