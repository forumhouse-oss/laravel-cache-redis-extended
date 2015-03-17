<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class MigrCreateFishTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @throws Exception
     */
    public function up()
    {
        Schema::create(
            'fish',
            function (Blueprint $table) {
                $table->increments('id');

                $table->integer('weight'); // we'll use this to demonstrate searching by weight
                $table->integer('bear_id'); // this will contain our foreign key to the bears table
            }
        );
    }

    public function down()
    {


    }
}