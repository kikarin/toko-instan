<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('stores', function (Blueprint $table) {
            if (! Schema::hasColumn('stores', 'is_active')) {
                $table->boolean('is_active')->default(true);
                $table->string('npwp')->nullable();
                $table->string('nik')->nullable();
                $table->boolean('is_pkp')->default(false);
                $table->string('tax_name')->nullable();
                $table->text('tax_address')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('stores', function (Blueprint $table) {
            $table->dropColumn(['is_active', 'npwp', 'nik', 'is_pkp', 'tax_name', 'tax_address']);
        });
    }
};
