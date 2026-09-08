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
        $arrivals_error = null;
        $departures_error = null;
        $notfound_error = null;

        if ($query !== '' && count($matches) === 0) {
            $notfound_error = "No airport found matching \"{$query}\".";
        } elseif (count($matches) === 1) {
            $airport = $matches[0];

            try {
                $data_arrivals = $this->openSky->arrivals($airport['icao']);
                $arrivals = collect($data_arrivals)->map(function ($flight) {
                    if (isset($flight['callsign'])) {
                        $flight['callsign'] = trim($flight['callsign']);
                    }
                    return $flight;
                })->toArray();
            } catch (PlaneException $e) {
                $arrivals_error = 'Could not load data right now, probably an API issue, please try again later.';
            }

            try {
                $data_departures = $this->openSky->departures($airport['icao']);
                $departures = collect($data_departures)->map(function ($flight) {
                    if (isset($flight['callsign'])) {
                        $flight['callsign'] = trim($flight['callsign']);
                    }
                    return $flight;
                })->toArray();
            } catch (PlaneException $e) {
                $departures_error = 'Could not load data right now, probably an API issue, please try again later.';
            }
        }

        return Inertia::render('aircraft/aircraft', [
            'query'           => $query,
            'matches'         => count($matches) > 1 ? $matches : [],
            'airport'         => $airport,
            'arrivals'        => $arrivals,
            'departures'      => $departures,
            'arrivals_error'   => $arrivals_error,
            'departures_error' => $departures_error,
            'error'           => $notfound_error,
        ]);
    }
}
