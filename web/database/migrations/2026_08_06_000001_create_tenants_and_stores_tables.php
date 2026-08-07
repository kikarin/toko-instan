<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('tenants')) {
            Schema::create('tenants', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->string('name');
                $table->string('slug')->unique();
                $table->string('plan')->default('free');
                $table->string('status')->default('active');
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('stores')) {
            Schema::create('stores', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
                $table->string('name');
                $table->string('slug')->unique();
                $table->string('category')->nullable();
                $table->text('description')->nullable();
                $table->string('logo')->nullable();
                $table->string('status')->default('active');
                $table->decimal('balance', 15, 2)->default(0);
                $table->decimal('pending_escrow', 15, 2)->default(0);
                $table->decimal('gmv', 15, 2)->default(0);
                $table->integer('total_orders')->default(0);
                $table->decimal('rating', 3, 1)->default(4.5);
                $table->string('badge')->nullable();
                $table->integer('avatar_hue')->default(220);
                $table->text('banner_url')->nullable();
                $table->string('phone')->nullable();
                $table->string('email')->nullable();
                $table->text('address')->nullable();
                $table->string('instagram')->nullable();
                $table->string('tiktok')->nullable();
                $table->string('headline')->nullable();
                $table->boolean('is_active')->default(true);
                $table->string('npwp')->nullable();
                $table->string('nik')->nullable();
                $table->boolean('is_pkp')->default(false);
                $table->string('tax_name')->nullable();
                $table->text('tax_address')->nullable();
                $table->timestamps();
            });
        } else {
            Schema::table('stores', function (Blueprint $table) {
                if (! Schema::hasColumn('stores', 'balance')) {
                    $table->decimal('balance', 15, 2)->default(0);
                    $table->decimal('pending_escrow', 15, 2)->default(0);
                    $table->decimal('gmv', 15, 2)->default(0);
                    $table->integer('total_orders')->default(0);
                    $table->decimal('rating', 3, 1)->default(4.5);
                    $table->string('badge')->nullable();
                    $table->integer('avatar_hue')->default(220);
                }
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('stores');
        Schema::dropIfExists('tenants');
    }
};
