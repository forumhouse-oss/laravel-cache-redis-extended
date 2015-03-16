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
        DB::table('bears_picnics')->delete();

        // seed our bears table -----------------------
        // we'll create three different bears

        // bear 1 is named Lawly. She is extremely dangerous. Especially when hungry.
        $bearLawly = Bear::create(array(
            'name' => 'Lawly',
            'type' => 'Grizzly',
            'danger_level' => 8
        ));

        // bear 2 is named Cerms. He has a loud growl but is pretty much harmless.
        $bearCerms = Bear::create(array(
            'name' => 'Cerms',
            'type' => 'Black',
            'danger_level' => 4
        ));

        // bear 3 is named Adobot. He is a polar bear. He drinks vodka.
        $bearAdobot = Bear::create(array(
            'name' => 'Adobot',
            'type' => 'Polar',
            'danger_level' => 3
        ));

        $this->command->info('The bears are alive!');

        // seed our fish table ------------------------
        // our fish wont have names... because theyre going to be eaten

        // we will use the variables we used to create the bears to get their id

        Fish::create(array(
            'weight' => 5,
            'bear_id' => $bearLawly->id
        ));
        Fish::create(array(
            'weight' => 12,
            'bear_id' => $bearCerms->id
        ));
        Fish::create(array(
            'weight' => 4,
            'bear_id' => $bearAdobot->id
        ));

        $this->command->info('They are eating fish!');

        // seed our trees table ---------------------
        Tree::create(array(
            'type' => 'Redwood',
            'age' => 500,
            'bear_id' => $bearLawly->id
        ));
        Tree::create(array(
            'type' => 'Oak',
            'age' => 400,
            'bear_id' => $bearLawly->id
        ));

        $this->command->info('Climb bears! Be free!');

        // seed our picnics table ---------------------

        // we will create one picnic and apply all bears to this one picnic
        $picnicYellowstone = Picnic::create(array(
            'name' => 'Yellowstone',
            'taste_level' => 6
        ));
        $picnicGrandCanyon = Picnic::create(array(
            'name' => 'Grand Canyon',
            'taste_level' => 5
        ));

        // link our bears to picnics ---------------------
        // for our purposes we'll just add all bears to both picnics for our many to many relationship
        $bearLawly->picnics()->attach($picnicYellowstone->id);
        $bearLawly->picnics()->attach($picnicGrandCanyon->id);

        $bearCerms->picnics()->attach($picnicYellowstone->id);
        $bearCerms->picnics()->attach($picnicGrandCanyon->id);

        $bearAdobot->picnics()->attach($picnicYellowstone->id);
        $bearAdobot->picnics()->attach($picnicGrandCanyon->id);

        $this->command->info('They are terrorizing picnics!');
    }
}
