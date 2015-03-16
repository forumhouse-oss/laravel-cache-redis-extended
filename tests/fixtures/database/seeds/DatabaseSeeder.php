<?php

namespace FHTeam\LaravelRedisCache\Tests\Fixtures\Database\Seeds;

use Eloquent;
use Seeder;

class DatabaseSeeder extends Seeder
{

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Eloquent::unguard();

        // call our class and run our seeds
        $this->call(BearAppSeeder::class);
        $this->command->info('Bear app seeds finished.');
    }
}