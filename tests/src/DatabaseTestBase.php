<?php

namespace FHTeam\LaravelRedisCache\Tests;

use Exception;
use FHTeam\LaravelRedisCache\Tests\Fixtures\Database\Seeds\DatabaseSeeder;

class DatabaseTestBase extends TestBase
{
    public function setUp()
    {
        parent::setUp();

        $migrationsPath = 'tests/fixtures/Database/Migrations';

        if (!$migrationsPath) {
            throw new Exception("Migrations path does not exist");
        }

        $this->artisan('migrate', [
            '--database' => 'test',
            '--path' => $migrationsPath,
        ]);

        $this->artisan('db:seed', [
            '--class' => DatabaseSeeder::class
        ]);

    }
}
