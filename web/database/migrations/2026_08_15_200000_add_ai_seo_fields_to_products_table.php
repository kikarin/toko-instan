<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            if (! Schema::hasColumn('products', 'meta_title')) {
                $table->string('meta_title', 80)->nullable()->after('description');
            }
            if (! Schema::hasColumn('products', 'meta_description')) {
                $table->string('meta_description', 180)->nullable()->after('meta_title');
            }
            if (! Schema::hasColumn('products', 'seo_tags')) {
                $table->string('seo_tags', 255)->nullable()->after('meta_description');
            }
            if (! Schema::hasColumn('products', 'marketing_caption')) {
                $table->text('marketing_caption')->nullable()->after('seo_tags');
            }
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            foreach (['meta_title', 'meta_description', 'seo_tags', 'marketing_caption'] as $col) {
                if (Schema::hasColumn('products', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
