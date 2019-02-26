<?php

use App\Http\Controllers\LocationsController;
use App\Location;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LocationsTableSeeder extends Seeder
{
    /**
     * Path to the CSV file with all Coffee Drop locations and times.
     *
     * @var string
     */
    protected $locationDataFile = '../location_data.csv';

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Open locations data file and loop through the file line by line adding new locations.
        if ( ( $handle = fopen( $this->locationDataFile, "r" ) ) !== FALSE ) {
            // Actual line
            $i = 1;

            while ( ( $data = fgetcsv( $handle, 1000, "," ) ) !== FALSE ) {
                // Skip first line containing headers.
                if ( $i === 1 ) {
                    $i++;
                    continue;
                }

                // Add new location.
                $postcode = $data[ 0 ];
                $latLng = LocationsController::getLatLng( $postcode );
                $location = Location::create( [
                    'postcode' => $postcode,
                    'lat' => $latLng[ 'lat' ] ?? null,
                    'lng' => $latLng[ 'lng' ] ?? null
                ] );

                // Get opening and closing times for each day,
                // Add all times to the location.
                $locationTimes = [
                    'Mon' => [ $data[ 1 ], $data[ 8 ] ],
                    'Tue' => [ $data[ 2 ], $data[ 9 ] ],
                    'Wed' => [ $data[ 3 ], $data[ 10 ] ],
                    'Thu' => [ $data[ 4 ], $data[ 11 ] ],
                    'Fri' => [ $data[ 5 ], $data[ 12 ] ],
                    'Sat' => [ $data[ 6 ], $data[ 13 ] ],
                    'Sun' => [ $data[ 7 ], $data[ 14 ] ],
                ];
                foreach ( $locationTimes as $day => $times ) {
                    if ( !empty( $times[ 0 ] ) && !empty( $times[ 1 ] ) ) {
                        $location->openingTimes()->create( [
                            'day' => $day,
                            'open_time' => $times[ 0 ],
                            'close_time' => $times[ 1 ]
                        ] );
                    }
                }

                // Increase line number.
                $i++;
            }
        }
    }
}
