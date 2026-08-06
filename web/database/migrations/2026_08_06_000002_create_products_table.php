<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('products')) {
            Schema::create('products', function (Blueprint $table) {
                $table->id();
                $table->foreignId('store_id')->constrained()->cascadeOnDelete();
                $table->string('name');
                $table->string('slug');
                $table->string('category');
                $table->decimal('price', 15, 2);
                $table->integer('sold')->default(0);
                $table->decimal('rating', 3, 1)->default(4.8);
                $table->string('tag')->nullable();
                $table->string('img')->nullable();
                $table->integer('stock')->default(100);
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
