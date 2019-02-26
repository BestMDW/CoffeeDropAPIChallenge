<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateLocationsOpeningTimesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create( 'locations_opening_times', function ( Blueprint $table ) {
            $table->increments( 'id' );
            $table->integer( 'location_id' )->unsigned();
            $table->enum( 'day', [ 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun' ] );
            $table->string( 'open_time' );
            $table->string( 'close_time' );
            $table->timestamps();

            $table->foreign( 'location_id' )->references( 'id' )->on( 'locations' );
        } );
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('locations_opening_times');
    }
}
