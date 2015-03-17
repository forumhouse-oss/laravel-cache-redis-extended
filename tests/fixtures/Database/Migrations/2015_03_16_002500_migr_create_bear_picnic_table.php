<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class MigrCreateBearPicnicTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @throws Exception
     */
    public function up()
    {
        Schema::create(
            'bear_picnic',
            function (Blueprint $table) {
                $table->increments('id');

                $table->integer('bear_id'); // the id of the bear
                $table->integer('picnic_id'); // the id of the picnic that this bear is at
            }
        );
    }

    public function down()
    {


    }
}
