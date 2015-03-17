<?php

namespace FHTeam\LaravelRedisCache\Tests\Fixtures\Database\Models;

use Eloquent;

class Picnic extends Eloquent
{

    public $timestamps = false;

    // MASS ASSIGNMENT -------------------------------------------------------
    // define which attributes are mass assignable (for security)
    // we only want these 3 attributes able to be filled
    protected $fillable = array('name', 'taste_level');

    // DEFINE RELATIONSHIPS --------------------------------------------------
    // define a many to many relationship
    // also call the linking table
    public function bears()
    {
        return $this->belongsToMany(Bear::class);
    }
}
