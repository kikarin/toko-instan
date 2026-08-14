<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('login_otps', function (Blueprint $table) {
            $table->id();
            $table->string('email');
            $table->unsignedBigInteger('store_id')->nullable();
            $table->string('code', 64);
            $table->timestamp('expires_at');
            $table->timestamps();

            $table->unique(['email', 'store_id']);
            $table->index(['email', 'store_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('login_otps');
    }
};
