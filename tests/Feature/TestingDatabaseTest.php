<?php

namespace Tests\Feature;

use Tests\TestCase;

class TestingDatabaseTest extends TestCase
{
    public function test_la_suite_usa_sqlite_en_memoria(): void
    {
        $driver = \Illuminate\Support\Facades\DB::connection()->getDriverName();

        $this->assertSame('sqlite', config('database.default'));
        $this->assertSame('sqlite', $driver);
        $this->assertSame(':memory:', config('database.connections.sqlite.database'));
        $this->assertTrue(blank(config('database.connections.sqlite.url')));
    }
}
