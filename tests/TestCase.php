<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        $cachedConfigPath = dirname(__DIR__).'/bootstrap/cache/config.php';

        if (is_file($cachedConfigPath)) {
            $cachedConfig = require $cachedConfigPath;
            $defaultConnection = $cachedConfig['database']['default'] ?? null;
            $sqliteDatabase = $cachedConfig['database']['connections']['sqlite']['database'] ?? null;

            if ($defaultConnection !== 'sqlite' || $sqliteDatabase !== ':memory:') {
                throw new \RuntimeException(
                    'Refusing to run tests with a cached non-test database configuration. Run php artisan config:clear first.'
                );
            }
        }

        parent::setUp();

        if (
            ! app()->environment('testing')
            || config('database.default') !== 'sqlite'
            || config('database.connections.sqlite.database') !== ':memory:'
        ) {
            throw new \RuntimeException(
                'Automated tests must use the isolated in-memory SQLite database.'
            );
        }
    }
}
