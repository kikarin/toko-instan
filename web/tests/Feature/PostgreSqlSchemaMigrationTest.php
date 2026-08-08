<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;

test('postgresql schema migration creates audit and logs schemas on pgsql drivers', function () {
    $migrationPath = database_path('migrations/2026_08_08_041254_create_postgresql_audit_and_logs_schemas.php');

    expect(File::exists($migrationPath))->toBeTrue();

    $contents = File::get($migrationPath);

    expect($contents)
        ->toContain("DB::getDriverName() !== 'pgsql'")
        ->toContain('CREATE SCHEMA IF NOT EXISTS audit')
        ->toContain('CREATE SCHEMA IF NOT EXISTS logs');

    $driver = DB::getDriverName();

    expect(in_array($driver, ['sqlite', 'pgsql'], true))->toBeTrue();

    if ($driver === 'pgsql') {
        $schemas = collect(DB::select('SELECT schema_name FROM information_schema.schemata'))
            ->pluck('schema_name')
            ->all();

        expect($schemas)->toContain('audit')->toContain('logs');
    }
});

test('order_items table exists after migrations', function () {
    expect(Schema::hasTable('order_items'))->toBeTrue();
    expect(Schema::hasColumns('order_items', [
        'id',
        'tenant_id',
        'order_id',
        'product_id',
        'product_variant_id',
        'name',
        'sku',
        'price',
        'qty',
        'total',
    ]))->toBeTrue();
});

test('env example defaults to pgsql with sqlite documented as local fallback', function () {
    $envExample = File::get(base_path('.env.example'));

    expect($envExample)
        ->toContain('DB_CONNECTION=pgsql')
        ->toContain('DB_PORT=5432')
        ->toContain('Local-only SQLite fallback');
});
