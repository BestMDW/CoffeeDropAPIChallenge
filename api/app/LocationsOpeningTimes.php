<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class LocationsOpeningTimes extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [ 'day', 'open_time', 'close_time' ];
}
