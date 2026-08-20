<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            if (! Schema::hasColumn('tenants', 'referral_code')) {
                $table->string('referral_code', 16)->nullable()->unique();
            }
            if (! Schema::hasColumn('tenants', 'referred_by_tenant_id')) {
                $table->foreignId('referred_by_tenant_id')->nullable()->constrained('tenants')->nullOnDelete();
            }
        });

        Schema::table('stores', function (Blueprint $table) {
            if (! Schema::hasColumn('stores', 'custom_domain')) {
                $table->string('custom_domain')->nullable()->unique();
            }
            if (! Schema::hasColumn('stores', 'custom_domain_status')) {
                $table->string('custom_domain_status', 20)->nullable();
            }
        });

        Schema::create('referral_clicks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->string('code', 16);
            $table->string('ip', 45)->nullable();
            $table->string('user_agent', 255)->nullable();
            $table->timestamps();
            $table->index(['tenant_id', 'created_at']);
        });

        Schema::create('referral_commissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('from_tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('order_id')->nullable()->constrained()->nullOnDelete();
            $table->unsignedInteger('amount');
            $table->string('status', 20)->default('credited');
            $table->timestamps();
            $table->unique(['tenant_id', 'order_id']);
        });

        Schema::create('store_faqs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('store_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->nullable()->constrained()->nullOnDelete();
            $table->string('question');
            $table->text('answer');
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->string('source', 20)->default('manual');
            $table->timestamps();
        });

        Schema::create('knowledge_articles', function (Blueprint $table) {
            $table->id();
            $table->string('slug', 120)->unique();
            $table->string('title');
            $table->string('category', 80)->default('Umum');
            $table->text('body');
            $table->boolean('is_published')->default(true);
            $table->timestamps();
        });

        Schema::create('support_tickets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('store_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('subject');
            $table->text('body');
            $table->string('status', 20)->default('open');
            $table->string('priority', 20)->default('normal');
            $table->timestamps();
            $table->index(['tenant_id', 'status']);
        });

        Schema::create('support_ticket_replies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('support_ticket_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->text('body');
            $table->boolean('is_staff')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('support_ticket_replies');
        Schema::dropIfExists('support_tickets');
        Schema::dropIfExists('knowledge_articles');
        Schema::dropIfExists('store_faqs');
        Schema::dropIfExists('referral_commissions');
        Schema::dropIfExists('referral_clicks');

        Schema::table('stores', function (Blueprint $table) {
            if (Schema::hasColumn('stores', 'custom_domain')) {
                $table->dropColumn(['custom_domain', 'custom_domain_status']);
            }
        });
        Schema::table('tenants', function (Blueprint $table) {
            if (Schema::hasColumn('tenants', 'referred_by_tenant_id')) {
                $table->dropConstrainedForeignId('referred_by_tenant_id');
            }
            if (Schema::hasColumn('tenants', 'referral_code')) {
                $table->dropColumn('referral_code');
            }
        });
    }
};
