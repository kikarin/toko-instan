<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            if (! Schema::hasColumn('products', 'description')) {
                $table->text('description')->nullable();
                $table->string('sku')->nullable();
                $table->string('brand')->default('Nike');
                $table->integer('weight_gram')->default(500);
            }
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['description', 'sku', 'brand', 'weight_gram']);
        });
    }
};
