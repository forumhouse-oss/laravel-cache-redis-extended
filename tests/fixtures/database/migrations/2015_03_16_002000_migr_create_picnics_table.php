<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class MigrCreatePicnicsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @throws Exception
     */
    public function up()
    {
        Schema::create('picnics', function (Blueprint $table) {
            $table->increments('id');

            $table->string('name');
            $table->integer('taste_level'); // how tasty is this picnic?

            $table->timestamps();
        });
    }

    public function down()
    {


    }
}
