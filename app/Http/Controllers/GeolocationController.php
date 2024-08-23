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

            // Validate the incoming data
            $validated = $request->validate([
                'start.lat' => 'required|numeric',
                'start.lng' => 'required|numeric',
                'end.lat' => 'required|numeric',
                'end.lng' => 'required|numeric',
            ]);

            // Store the data in the database
            $location = new Coordinate();
            $location->start_location = json_encode($validated['start']);
            $location->end_location = json_encode($validated['end']);
            $location->save();

            return response()->json([
                'message' => 'Data stored successfully',
                'data' => $location,
                'status' => 'success',
            ], 200);

        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return response()->json(['error' => 'An error occurred'], 500);
        }
    }

}
