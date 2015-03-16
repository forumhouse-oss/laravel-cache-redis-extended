<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class MigrCreateBearsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @throws Exception
     */
    public function up()
    {
        Schema::create('bears', function (Blueprint $table) {
            $table->increments('id');

            $table->string('name');
            $table->string('type');
            $table->integer('danger_level'); // this will be between 1-10

            $table->timestamps();
        });
    }

    public function down()
    {


    }
}