<?php

namespace FHTeam\LaravelRedisCache\Tests\Fixtures\Database\Models;

use Eloquent;

class Bear extends Eloquent
{
    // MASS ASSIGNMENT -------------------------------------------------------
    // define which attributes are mass assignable (for security)
    // we only want these 3 attributes able to be filled
    protected $fillable = array('name', 'type', 'danger_level');

    public $timestamps = false;

    // DEFINE RELATIONSHIPS --------------------------------------------------
    // each bear HAS one fish to eat
    public function fish()
    {
        return $this->hasOne(Fish::class); // this matches the Eloquent model
    }

    // each bear climbs many trees
    public function trees()
    {
        return $this->hasMany(Tree::class);
    }

    // each bear BELONGS to many picnic
    // define our pivot table also
    public function picnics()
    {
        return $this->belongsToMany(Picnic::class);
    }
}
