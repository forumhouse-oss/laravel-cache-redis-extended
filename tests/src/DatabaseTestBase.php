<?php

namespace FHTeam\LaravelRedisCache\Tests;

use Exception;
use FHTeam\LaravelRedisCache\Tests\Fixtures\Database\Seeds\DatabaseSeeder;

class DatabaseTestBase extends TestBase
{
    protected $migrationsPath = '../fixtures/Database/Migrations';

    public function setUp()
    {
        parent::setUp();

        //TODO: validate directory existence
        if (!$this->migrationsPath) {
            throw new Exception("Migrations path does not exist");
        }

        $this->artisan(
            'migrate',
            [
                '--database' => 'test',
                '--path' => $this->migrationsPath,
            ]
        );

        $this->artisan(
            'db:seed',
            [
                '--class' => DatabaseSeeder::class
            ]
        );
    }
}
