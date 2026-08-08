<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('stores', function (Blueprint $table) {
            if (Schema::hasColumn('stores', 'theme_color')) {
                $table->dropColumn('theme_color');
            }
            if (! Schema::hasColumn('stores', 'theme_colors')) {
                $table->json('theme_colors')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('stores', function (Blueprint $table) {
            if (Schema::hasColumn('stores', 'theme_colors')) {
                $table->dropColumn('theme_colors');
            }
            if (! Schema::hasColumn('stores', 'theme_color')) {
                $table->string('theme_color')->nullable();
            }
        });
    }
};
