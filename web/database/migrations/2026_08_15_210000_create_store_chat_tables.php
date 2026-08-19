<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('store_chats', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('store_id')->constrained()->cascadeOnDelete();
            $table->string('session_key', 64);
            $table->string('visitor_name')->nullable();
            $table->timestamps();
            $table->unique(['store_id', 'session_key']);
        });

        Schema::create('store_chat_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_chat_id')->constrained('store_chats')->cascadeOnDelete();
            $table->string('role', 20);
            $table->text('body');
            $table->timestamps();
            $table->index(['store_chat_id', 'id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('store_chat_messages');
        Schema::dropIfExists('store_chats');
    }
};
