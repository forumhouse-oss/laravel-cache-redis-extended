<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class MigrCreateTreesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @throws Exception
     */
    public function up()
    {
        Schema::create(
            'trees',
            function (Blueprint $table) {
                $table->increments('id');

                $table->string('type');
                $table->integer('age'); // how old is the tree
                $table->integer('bear_id'); // which bear climbs this tree
            }
        );
    }

    public function down()
    {


    }
}
