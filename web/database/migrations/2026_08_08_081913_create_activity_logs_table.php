<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if ($this->isPostgres()) {
            DB::statement('CREATE SCHEMA IF NOT EXISTS audit');

            Schema::create('audit.activity_logs', function (Blueprint $table) {
                $this->activityLogTable($table);
            });

            return;
        }

        Schema::create('activity_logs', function (Blueprint $table) {
            $this->activityLogTable($table);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists($this->tableName());
    }

    protected function isPostgres(): bool
    {
        return DB::connection()->getDriverName() === 'pgsql';
    }

    protected function tableName(): string
    {
        return $this->isPostgres() ? 'audit.activity_logs' : 'activity_logs';
    }

    protected function activityLogTable(Blueprint $table): void
    {
        $table->id();
        $table->foreignId('tenant_id')->nullable()->constrained()->cascadeOnDelete();
        $table->foreignId('user_id')->nullable()->constrained()->cascadeOnDelete();
        $table->string('action');
        $table->string('subject_type')->nullable();
        $table->string('subject_id')->nullable();
        $table->json('properties')->nullable();
        $table->string('ip')->nullable();
        $table->timestamp('created_at');

        $table->index(['tenant_id', 'created_at']);
        $table->index(['subject_type', 'subject_id']);
        $table->index('action');
    }
};
