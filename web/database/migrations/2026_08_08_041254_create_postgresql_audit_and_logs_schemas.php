<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Prepare PostgreSQL schemas used for audit trail and application logs.
     * No-op on SQLite (local/tests) — schemas are a PostgreSQL concept.
     */
    public function up(): void
    {
        if (DB::getDriverName() !== 'pgsql') {
            return;
        }

        DB::statement('CREATE SCHEMA IF NOT EXISTS audit');
        DB::statement('CREATE SCHEMA IF NOT EXISTS logs');
    }

    public function down(): void
    {
        if (DB::getDriverName() !== 'pgsql') {
            return;
        }

        DB::statement('DROP SCHEMA IF EXISTS logs CASCADE');
        DB::statement('DROP SCHEMA IF EXISTS audit CASCADE');
    }
};
