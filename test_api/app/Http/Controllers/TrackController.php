<?php

namespace App\Http\Controllers;

use App\Exceptions\PlaneException;
use App\Services\OpenSky\OpenSkyClient;
use Illuminate\Http\JsonResponse;

class TrackController extends Controller
{
    public function __construct(private OpenSkyClient $openSky) {}

    /**
     * JSON, not Inertia — called via fetch() from the flights table so
     * the map expands in place instead of navigating to a new page.
     */
    public function show(string $icao24): JsonResponse
    {
        try {
            return response()->json($this->openSky->track($icao24));
        } catch (PlaneException $e) {
            return response()->json(['error' => 'No track found for this flight.'], 422);
        }
    }
}
