<?php

namespace App\Http\Controllers;

use App\Models\Coordinate;
use Illuminate\Http\Request;
use Google_Client;
use Google_Service_Geocoding;
use Illuminate\Support\Facades\Log;

class GeolocationController extends Controller
{
    public function index()
    {
        return view('geolocation');
    }

    public function getLocation(Request $request)
{
    try {
        $latitude = $request->input('latitude');
        $longitude = $request->input('longitude');

        // Validate and process coordinates as needed
        // For example, you might want to check if they are numeric
        if (!is_numeric($latitude) || !is_numeric($longitude)) {
            return response()->json(['error' => 'Invalid coordinates'], 400);
        }

        // Store the coordinates or perform other actions
        // Assuming you have a Coordinate model to save the data
        Coordinate::create([
            'latitude' => $latitude,
            'longitude' => $longitude
        ]);

        return response()->json(['latitude' => $latitude, 'longitude' => $longitude]);

    } catch (\Exception $e) {
        // Log the exception for debugging
        Log::error($e->getMessage());

        return response()->json(['error' => 'An error occurred'], 500);
    }
}

}
