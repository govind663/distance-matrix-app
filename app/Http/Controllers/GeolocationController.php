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
            $data = $request->json()->all();
            $latitude = $data['latitude'];
            $longitude = $data['longitude'];

            if (!is_numeric($latitude) || !is_numeric($longitude)) {
                return response()->json(['error' => 'Invalid coordinates'], 400);
            }

            Coordinate::create([
                'latitude' => $latitude,
                'longitude' => $longitude
            ]);

            return response()->json(['latitude' => $latitude, 'longitude' => $longitude]);

        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return response()->json(['error' => 'An error occurred'], 500);
        }
    }

}
