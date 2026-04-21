<?php

namespace Tests\Feature;

use Tests\TestCase;

class TestDatabaseSafetyTest extends TestCase
{
    public function test_phpunit_uses_the_sqlite_test_database(): void
    {
        $connection = config('database.default');

        $this->assertSame('sqlite', $connection);
        $this->assertStringEndsWith(
            'database/testing.sqlite',
            (string) config("database.connections.{$connection}.database")
        );
    }
}
