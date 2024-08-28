<?php

namespace App\Http\Controllers;

use App\Models\Coordinate;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
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

            // Debugging: Log validated data
            Log::info('Validated data:', $validated);

            // Store the data in the database
            $location = new Coordinate();
            $location->user_id = Auth::user()->id; // Assuming Auth facade is used for authentication
            $location->start_location = json_encode($validated['start']);
            $location->end_location = json_encode($validated['end']);
            $location->inserted_at = Carbon::now();
            $location->save();

            $update = [
                'inserted_by' => $location->id,
            ];

            Coordinate::where('id', $location->id)->update($update);

            // Debugging: Log success message
            Log::info('Data stored successfully:', ['location' => $location]);

            return response()->json([
                'message' => 'Data stored successfully',
                'data' => $location,
                'status' => 'success',
            ], 200);

        } catch (\Exception $e) {
            // Log the exception message
            Log::error('Error storing data: ' . $e->getMessage());

            return response()->json(['error' => 'An error occurred'], 500);
        }
    }

    public function deleteLocation(Request $request) {
        try {
            // Validate the incoming data
            $validated = $request->validate([
                'start.lat' => 'required|numeric',
                'start.lng' => 'required|numeric',
                'end.lat' => 'required|numeric',
                'end.lng' => 'required|numeric',
            ]);

            // Find the record for the authenticated user
            $record = Coordinate::where('user_id', Auth::user()->id)
                ->where('start_location', json_encode($validated['start']))
                ->where('end_location', json_encode($validated['end']))
                ->first();

            if ($record) {
                // Delete the record
                DB::table('locations')->where('id', $record->id)->delete();

                // Log success message
                Log::info('Location record deleted successfully', ['location_id' => $record->id]);

                return response()->json([
                    'message' => 'Location record deleted successfully',
                    'status' => 'success',
                ], 200);
            } else {
                // Record not found
                return response()->json(['error' => 'Location record not found'], 404);
            }
        } catch (\Exception $e) {
            // Log the exception message
            Log::error('Error deleting location record: ' . $e->getMessage());

            return response()->json(['error' => 'An error occurred'], 500);
        }
    }


}
