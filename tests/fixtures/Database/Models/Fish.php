<?php

namespace FHTeam\LaravelRedisCache\Tests\Fixtures\Database\Models;

use Eloquent;

class Fish extends Eloquent
{

    public $timestamps = false;

    // MASS ASSIGNMENT -------------------------------------------------------
    // define which attributes are mass assignable (for security)
    // we only want these 3 attributes able to be filled
    protected $fillable = array('weight', 'bear_id');

    // LINK THIS MODEL TO OUR DATABASE TABLE ---------------------------------
    // since the plural of fish isnt what we named our database table we have to define it
    protected $table = 'fish';

    // DEFINE RELATIONSHIPS --------------------------------------------------
    public function bear()
    {
        return $this->belongsTo(Bear::class);
    }

}