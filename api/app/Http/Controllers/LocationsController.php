<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateNewLocationRequest;
use App\Location;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LocationsController extends Controller
{
    /**
     * Three letters of the name of the day.
     *
     * @var array
     */
    protected $daysShort = [
        'monday' => 'Mon',
        'tuesday' => 'Tue',
        'wednesday' => 'Wed',
        'thursday' => 'Thu',
        'friday' => 'Fri',
        'saturday' => 'Sat',
        'Sunday' => 'Sun'
    ];

    /**
     * Available coffees of capsules.
     *
     * @var array
     */
    protected $coffees = [ 'Ristretto', 'Espresso', 'Lungo' ];

    /**
     * Cashback in pence of all range of coffees amount [<50, 50-500, >500]
     *
     * @var array
     */
    protected $cashback = [
        'Ristretto' => [ 2, 3, 5 ],
        'Espresso' => [ 4, 6, 10 ],
        'Lungo' => [ 6, 9, 15 ]
    ];

    /**
     * Store a newly created resource in storage.
     *
     * @param CreateNewLocationRequest $request
     * @return JsonResponse
     */
    public function storeNewLocation(CreateNewLocationRequest $request) : JsonResponse
    {
        // No opening times in the request,
        // Set status code and send error message.
        if ( empty( $request->opening_times ) || empty( $request->closing_times ) ) {
            return response()->json( [
                    'statusCode' => 400,
                    'error' => 'Please provide opening and closing times.'
                ], 400 );
        }

        // Get postcode from the request.
        $postcode = $request->postcode;

        // Validate the postcode.
        if ( !self::validatePostcode( $postcode ) ) {
            return response()->json( [
                'statusCode' => 400,
                'error' => 'The postcode is not valid.'
            ], 400 );
        }

        // Figure out latitude and longitude.
        $latLng = self::getLatLng( $postcode );
        if ( !$latLng ) {
            return response()->json( [
                'statusCode' => 500,
                'error' => 'We couldn\'t check the postcode. Please try again.'
            ], 500 );
        }

        // Create new location and opening times.
        $location = Location::create( [
            'postcode' => $postcode,
            'lat' => $latLng[ 'lat' ],
            'lng' => $latLng[ 'lng' ]
        ] );
        $openingTimes = $this->makeOpeningTimes( $request->opening_times, $request->closing_times );
        foreach ( $openingTimes as $day => $times ) {
            $location->openingTimes()->create( [
                'day' => $day,
                'open_time' => $times[ 0 ],
                'close_time' => $times[ 1 ]
            ] );
        }

        return response()->json( $location, 201 );
    }

    /**
     * Find and return nearest location.
     *
     * @param string $postcode
     * @return JsonResponse
     */
    public function getNearestLocation( string $postcode ) : JsonResponse
    {
        // Check if the given postcode is valid.
        // Finish early and return error message if not.
        $validate = self::validatePostcode( $postcode );
        if ( !$validate ) {
            return response()->json( [
                'statusCode' => 400,
                'error' => 'Please provide a valid postcode!'
            ] );
        }

        // Get latitude and longitude and calculate the nearest location.
        // Finish early and return error message if response failed.
        $latLng = self::getLatLng( $postcode );
        if ( !$latLng ) {
            return response()->json( [
                'statusCode' => 400,
                'error' => 'We couldn\'t process your query. Please try again.'
            ] );
        }

        // Destructure lat and lng from the array.
        [ "lat" => $lat, "lng" => $lng ] = $latLng;

        // Haversine formula for calculating distance between current location and Coffee Drop locations.
        $distance = "( 3959 * acos( cos( radians($lat) ) * cos( radians( lat ) ) * cos( radians( lng ) - radians($lng) ) + sin( radians($lat) ) * sin(radians(lat)) ) )";

        // The postcode is valid and we have a data important to find the nearest location.
        // Search locations within 30 miles of the postcode location.
        $nearestLocation = Location::select( 'id', 'postcode', 'lat', 'lng', DB::raw( "$distance AS distance" ) )
            ->whereNotNull( 'lat' )
            ->whereNotNull( 'lng' )
            ->orderBy( 'distance', 'ASC' )
            ->with( [
                'openingTimes' => function ( $query ) {
                    $query->select( 'id', 'location_id', 'day', 'open_time', 'close_time' );
                }
            ] )
            ->first();

        return response()->json( $nearestLocation );
    }

    /**
     * Calculate and return total cashback of the requested order.
     *
     * @param Request $request
     * @return float
     */
    public function CalculateCashback( Request $request ) : float
    {
        $totalCashback = 0;

        // Check all available coffees types in the request.
        foreach ( $this->coffees as $coffee ) {
            // Calculate only when coffee is available and amount is integer value.
            if ( isset( $request->$coffee ) && is_int( $request->$coffee ) ) {
                $amount = $request->$coffee;

                // Figure out the amount and cashback of the given coffee.
                if ( $amount > 0 && $amount <= 50 ) {
                    $totalCashback += $amount * $this->cashback[ $coffee ][ 0 ];
                } elseif ( $amount > 50 && $amount <= 500 ) {
                    $totalCashback += $amount * $this->cashback[ $coffee ][ 1 ];
                } elseif ( $amount > 500 ) {
                    $totalCashback += $amount * $this->cashback[ $coffee ][ 2 ];
                }
            }
        }

        // Save request in the database.
        $this->saveRequest( $request, $totalCashback );

        // Convert to pounds and return.
        return money_format( '%.2n', $totalCashback / 100 );
    }

    /**
     * Create new request data in the database.
     *
     * @param Request $request
     * @param int $cashback
     * @return bool
     */
    protected function saveRequest( Request $request, int $cashback ) : bool
    {
        $jsonRequest = json_encode( $request->all() );

        \App\Request::create( [
            'user_ip' => $_SERVER[ 'REMOTE_ADDR' ],
            'user_agent' => $_SERVER[ 'HTTP_USER_AGENT' ],
            'request' => $jsonRequest,
            'cashback' => $cashback
        ] );

        return true;
    }

    /**
     * Returns array with opening and closing times grouped by days.
     *
     * @param array $openingTimes
     * @param array $closingTimes
     * @return array
     */
    protected function makeOpeningTimes( array $openingTimes, array $closingTimes) : array
    {
        // Opening and closing times grouped by days.
        $output = [];

        // Add opening times to the output.
        foreach ( $openingTimes as $day => $time ) {
            $dayShort = $this->daysShort[ $day ];
            $output[ $dayShort ][ 0 ] = $time;
        }

        // Add closing times to the output.
        foreach ( $closingTimes as $day => $time ) {
            $dayShort = $this->daysShort[ $day ];
            $output[ $dayShort ][ 1 ] = $time;
        }

        return $output;
    }

    /**
     * Returns latitude and longitude of the {$postcode} or false if bad response.
     *
     * @param string $postcode
     * @return array|bool
     */
    public static function getLatLng( string $postcode )
    {
        $client = new \GuzzleHttp\Client();

        // Send GET request to the Postcodes endpoint and return latitude and longitude from the response.
        $response = $client->get( env( 'POSTCODES_API_ENDPOINT' ) . $postcode );

        if ( $response->getStatusCode() == 200 ) {
            $result = json_decode( $response->getBody() )->result;
            return [
                'lat' => $result->latitude,
                'lng' => $result->longitude
            ];
        } else {
            return false;
        }
    }

    /**
     * Return true if the {$postcode} is valid or false otherwise.
     *
     * @param $postcode
     * @return bool
     */
    public static function validatePostcode( string $postcode ) : bool
    {
        $client = new \GuzzleHttp\Client();

        $response = $client->get( env( 'POSTCODES_API_ENDPOINT' ) . $postcode . '/validate' );

        if ( $response->getStatusCode() == 200 ) {
            $result = json_decode( $response->getBody() )->result;

            return $result;
        } else {
            return false;
        }
    }
}
