<?php

namespace Tests;

use Illuminate\Contracts\Console\Kernel;

trait CreatesApplication
{
    /**
     * Creates the application.
     *
     * @return \Illuminate\Foundation\Application
     */
    public function createApplication()
    {
        if (file_exists(__DIR__.'/../bootstrap/cache/config.php')) {
            throw new \RuntimeException(
                'Refusing to run tests while bootstrap/cache/config.php exists. '.
                'Run php artisan config:clear so phpunit.xml can force the test database.'
            );
        }

        if (glob(__DIR__.'/../bootstrap/cache/routes-*.php')) {
            throw new \RuntimeException(
                'Refusing to run tests while cached routes exist. '.
                'Run php artisan route:clear so tests use current route definitions.'
            );
        }

        $app = require __DIR__.'/../bootstrap/app.php';

        $app->make(Kernel::class)->bootstrap();

        $connection = config('database.default');
        $database = config("database.connections.{$connection}.database");

        if ($connection !== 'sqlite' || ! str_ends_with((string) $database, 'database/testing.sqlite')) {
            throw new \RuntimeException(
                "Refusing to run tests against database connection [{$connection}] with database [{$database}]."
            );
        }

        return $app;
    }
}
