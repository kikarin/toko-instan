<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('username')->nullable()->unique()->after('name');
            $table->text('bio')->nullable()->after('email');
            $table->string('phone')->nullable()->after('bio');
            $table->timestamp('phone_verified_at')->nullable()->after('phone');
            $table->string('gender', 20)->nullable()->after('phone_verified_at');
            $table->string('birth_date', 50)->nullable()->after('gender');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'username',
                'bio',
                'phone',
                'phone_verified_at',
                'gender',
                'birth_date',
            ]);
        });
    }
};
