<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->string('type', 20)->default('physical')->after('description');
            $table->string('digital_file_path')->nullable()->after('type');
            $table->string('digital_file_name')->nullable()->after('digital_file_path');
            $table->string('digital_file_mime', 120)->nullable()->after('digital_file_name');
        });

        Schema::table('order_items', function (Blueprint $table) {
            $table->string('product_type', 20)->default('physical')->after('sku');
            $table->string('digital_file_path')->nullable()->after('product_type');
            $table->string('digital_file_name')->nullable()->after('digital_file_path');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['type', 'digital_file_path', 'digital_file_name', 'digital_file_mime']);
        });

        Schema::table('order_items', function (Blueprint $table) {
            $table->dropColumn(['product_type', 'digital_file_path', 'digital_file_name']);
        });
    }
};
