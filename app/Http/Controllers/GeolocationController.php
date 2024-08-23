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
            $latitude = $request->input('lat');
            $longitude = $request->input('lng');

            // Validate coordinates
            $validatedData = $request->validate([
                'lat' => 'required',
                'lng' => 'required',
            ]);

            // Process or store the coordinates
            $coordinate = new Coordinate();
            $coordinate->latitude = $latitude;
            $coordinate->longitude = $longitude;
            $coordinate->save();

            return response()->json([
                'latitude' => $latitude,
                'longitude' => $longitude
            ]);

        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return response()->json(['error' => 'An error occurred'], 500);
        }
    }

}
