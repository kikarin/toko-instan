<?php

namespace App\Console\Commands;

use Illuminate\Foundation\Console\ServeCommand as BaseServeCommand;

use function Illuminate\Support\php_binary;

/**
 * Same as Laravel's serve, but raises PHP upload limits on the php -S worker.
 *
 * Plain `php artisan serve` otherwise inherits CLI defaults (often 2M),
 * which breaks digital product uploads (~4MB+).
 */
class ServeCommand extends BaseServeCommand
{
    /**
     * @return array<int, string>
     */
    protected function serverCommand(): array
    {
        $server = file_exists(base_path('server.php'))
            ? base_path('server.php')
            : base_path('vendor/laravel/framework/src/Illuminate/Foundation/resources/server.php');

        return [
            php_binary(),
            '-d', 'upload_max_filesize=50M',
            '-d', 'post_max_size=60M',
            '-S',
            $this->host().':'.$this->port(),
            $server,
        ];
    }
}
