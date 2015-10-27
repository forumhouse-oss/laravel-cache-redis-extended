<?php

namespace FHTeam\LaravelRedisCache\Tests\Fixtures\Database\Seeds;

// our own seeder class
// usually this would be its own file
use DB;
use FHTeam\LaravelRedisCache\Tests\Fixtures\Database\Models\Bear;
use FHTeam\LaravelRedisCache\Tests\Fixtures\Database\Models\Fish;
use FHTeam\LaravelRedisCache\Tests\Fixtures\Database\Models\Picnic;
use FHTeam\LaravelRedisCache\Tests\Fixtures\Database\Models\Tree;
use Seeder;

class BearAppSeeder extends Seeder
{
    public function run()
    {
        // clear our database ------------------------------------------
        DB::table('bears')->delete();
        DB::table('fish')->delete();
        DB::table('picnics')->delete();
        DB::table('trees')->delete();
        DB::table('bear_picnic')->delete();

        // seed our bears table -----------------------
        $bearLawly = Bear::create(
            array(
                'id' => 1,
                'name' => 'Lawly',
                'type' => 'Grizzly',
                'danger_level' => 8,
            )
        );

        $bearCerms = Bear::create(
            array(
                'id' => 2,
                'name' => 'Cerms',
                'type' => 'Black',
                'danger_level' => 4,
            )
        );

        $bearAdobot = Bear::create(
            array(
                'id' => 3,
                'name' => 'Adobot',
                'type' => 'Polar',
                'danger_level' => 3,
            )
        );

        // seed our fish table ------------------------
        Fish::create(
            array(
                'id' => 1,
                'weight' => 5,
                'bear_id' => $bearLawly->id,
            )
        );
        Fish::create(
            array(
                'id' => 2,
                'weight' => 12,
                'bear_id' => $bearCerms->id,
            )
        );
        Fish::create(
            array(
                'id' => 3,
                'weight' => 4,
                'bear_id' => $bearAdobot->id,
            )
        );

        // seed our trees table ---------------------
        Tree::create(
            array(
                'id' => 1,
                'type' => 'Redwood',
                'age' => 500,
                'bear_id' => $bearLawly->id,
            )
        );
        Tree::create(
            array(
                'id' => 2,
                'type' => 'Oak',
                'age' => 400,
                'bear_id' => $bearLawly->id,
            )
        );

        // seed our picnics table ---------------------
        $picnicYellowstone = Picnic::create(
            array(
                'id' => 1,
                'name' => 'Yellowstone',
                'taste_level' => 6,
            )
        );
        $picnicGrandCanyon = Picnic::create(
            array(
                'id' => 2,
                'name' => 'Grand Canyon',
                'taste_level' => 5,
            )
        );

        // link our bears to picnics ---------------------
        $bearLawly->picnics()->attach($picnicYellowstone->id);
        $bearLawly->picnics()->attach($picnicGrandCanyon->id);

        $bearCerms->picnics()->attach($picnicYellowstone->id);
        $bearCerms->picnics()->attach($picnicGrandCanyon->id);

        $bearAdobot->picnics()->attach($picnicYellowstone->id);
        $bearAdobot->picnics()->attach($picnicGrandCanyon->id);

        $this->command->info('They are terrorizing picnics!');
    }
}
