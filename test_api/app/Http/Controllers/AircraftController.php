<?php

namespace App\Http\Controllers;

use App\Exceptions\PlaneException;
use App\Services\OpenSky\AirportNames;
use App\Services\OpenSky\OpenSkyClient;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Http\Request;

class AircraftController extends Controller
{
    public function __construct(private OpenSkyClient $openSky) {}

    public function index(Request $request): Response
    {
        $query = trim((string) $request->query('airport', ''));
        $matches = $query !== '' ? AirportNames::resolve($query) : [];
        $airport = null;
        $arrivals = [];
        $departures = [];
        $arrivalsError = null;
        $departuresError = null;
        $notFoundError = null;

        if ($query !== '' && count($matches) === 0) {
            $notFoundError = "No airport found matching \"{$query}\" — try a city name, IATA code, or ICAO code.";
        } elseif (count($matches) === 1) {
            $airport = $matches[0];

            try {
                $rawArrivals = $this->openSky->arrivals($airport['icao']);
                // Καθαρισμός των κενών από το callsign
                $arrivals = collect($rawArrivals)->map(function ($flight) {
                    if (isset($flight['callsign'])) {
                        $flight['callsign'] = trim($flight['callsign']);
                    }
                    return $flight;
                })->toArray();
            } catch (PlaneException $e) {
                $arrivalsError = 'Could not load data right now, probably an API issue, please try again later.';
            }

            try {
                $rawDepartures = $this->openSky->departures($airport['icao']);
                // Καθαρισμός των κενών από το callsign
                $departures = collect($rawDepartures)->map(function ($flight) {
                    if (isset($flight['callsign'])) {
                        $flight['callsign'] = trim($flight['callsign']);
                    }
                    return $flight;
                })->toArray();
            } catch (PlaneException $e) {
                $departuresError = 'Could not load departures right now — try again shortly.';
            }
        }

        return Inertia::render('aircraft/aircraft', [
            'query'           => $query,
            'matches'         => count($matches) > 1 ? $matches : [],
            'airport'         => $airport,
            'arrivals'        => $arrivals,
            'departures'      => $departures,
            'arrivalsError'   => $arrivalsError,
            'departuresError' => $departuresError,
            'error'           => $notFoundError,
        ]);
    }
}
