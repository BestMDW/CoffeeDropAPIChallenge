<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Location extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [ 'postcode', 'lat', 'lng' ];

    /**
     * Returns all available opening hours for the location.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function openingTimes()
    {
        return $this->hasMany( '\App\LocationsOpeningTimes' );
    }
}
