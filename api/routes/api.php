<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

/* GET Locations */
Route::get('/GetNearestLocation/{postcode}', 'LocationsController@GetNearestLocation');

/* POST Locations */
Route::post('/CreateNewLocation', 'LocationsController@StoreNewLocation');
Route::post('/CalculateCashback', 'LocationsController@CalculateCashback');
