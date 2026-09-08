<?php

namespace App\Services\OpenSky;

use App\Services\OpenSky\AirplaneNames;
use App\Services\OpenSky\AirportNames;
use App\Exceptions\PlaneException;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;


class OpenSkyClient
{
    private string $baseUrl;
    private string $tokenUrl;
    private ?string $clientId;
    private ?string $clientSecret;
    private int $timeout;


    public function arrivals(string $airportIcao): array
    {
        $end = now('UTC')->startOfDay();
        $begin = $end->copy()->subDay();

        $request = Http::timeout($this->timeout);
        if ($this->clientId && $this->clientSecret) {
            $request = $request->withToken($this->accessToken());
        }

        try {
            $response = $request->get("{$this->baseUrl}/flights/arrival", [
                'airport' => strtoupper($airportIcao),
                'begin'   => $begin->timestamp,
                'end'     => $end->timestamp,
            ]);
        } catch (ConnectionException $e) {
            Log::error('OpenSky arrivals connection failed', ['error' => $e->getMessage()]);
            throw new PlaneException("Could not reach OpenSky: {$e->getMessage()}");
        }

        if ($response->status() === 404) {
            return []; // genuinely "no flights", not a failure
        }

        if ($response->failed()) {
            Log::warning('OpenSky arrivals request failed', ['status' => $response->status()]);
            throw new PlaneException("OpenSky error: HTTP {$response->status()}");
        }

        return collect($response->json() ?? [])
            ->filter(fn(array $f) => $this->looksCommercial($f['callsign'] ?? null))
            ->map(fn(array $f) => [
                'icao24'           => $f['icao24'],
                'callsign'         => trim($f['callsign'] ?? ''),
                'airline'          => AirplaneNames::nameFor($f['callsign'] ?? null),
                'departureAirport' => AirportNames::cityFor($f['estDepartureAirport']),
                'arrivalAirport'   => AirportNames::cityFor($f['estArrivalAirport']),
                'landedAt'         => isset($f['lastSeen'])
                    ? Carbon::createFromTimestamp($f['lastSeen'], 'UTC')->toDateTimeString() . ' UTC'
                    : null,
            ])
            ->sortByDesc('landedAt')
            ->values()
            ->all();
    }

    /////////////

    private function looksCommercial(?string $callsign): bool
    {
        return $callsign && preg_match('/^[A-Z]{3}\d{1,4}[A-Z]?$/', trim($callsign));
    }

    public function __construct()
    {
        $this->baseUrl = rtrim(config('services.opensky.base_url'), '/');
        $this->tokenUrl = config('services.opensky.token_url');
        $this->clientId = config('services.opensky.client_id');
        $this->clientSecret = config('services.opensky.client_secret');
        $this->timeout = (int) config('services.opensky.timeout', 10);
    }

    public function liveStates(?array $bbox = null): array
    {
        $request = Http::timeout($this->timeout);

        if ($this->clientId && $this->clientSecret) {
            $request = $request->withToken($this->accessToken());
        }

        try {
            $response = $request->get("{$this->baseUrl}/states/all", $bbox ?? []);
        } catch (ConnectionException $e) {
            Log::error('OpenSky connection failed', ['error' => $e->getMessage()]);
            throw new PlaneException("Could not reach OpenSky: {$e->getMessage()}");
        }

        if ($response->failed()) {
            Log::warning('OpenSky request failed', ['status' => $response->status()]);
            throw new PlaneException("OpenSky error: HTTP {$response->status()}");
        }

        return array_map(fn(array $s) => [
            'icao24'        => $s[0],
            'callsign'      => $s[1] ? trim($s[1]) : null,
            'originCountry' => $s[2],
            'longitude'     => $s[5],
            'latitude'      => $s[6],
            'altitude'      => $s[7],
            'onGround'      => $s[8],
            'velocity'      => $s[9],
            'heading'       => $s[10],
            'verticalRate'  => $s[11],
        ], $response->json('states') ?? []);
    }

    ///////////////

    private function accessToken(): string
    {
        return Cache::remember('opensky_access_token', now()->addMinutes(25), function () {
            try {
                $response = Http::asForm()->timeout($this->timeout)->post($this->tokenUrl, [
                    'grant_type'    => 'client_credentials',
                    'client_id'     => $this->clientId,
                    'client_secret' => $this->clientSecret,
                ]);
            } catch (ConnectionException $e) {
                throw new PlaneException("Could not reach OpenSky auth server: {$e->getMessage()}");
            }

            if ($response->failed()) {
                throw new PlaneException("OpenSky auth failed: HTTP {$response->status()}");
            }

            return $response->json('access_token');
        });
    }

    /////////////////

    public function departures(string $airportIcao): array
    {
        $end = now('UTC')->startOfDay();
        $begin = $end->copy()->subDay();

        $request = Http::timeout($this->timeout);
        if ($this->clientId && $this->clientSecret) {
            $request = $request->withToken($this->accessToken());
        }

        try {
            $response = $request->get("{$this->baseUrl}/flights/departure", [
                'airport' => strtoupper($airportIcao),
                'begin'   => $begin->timestamp,
                'end'     => $end->timestamp,
            ]);
        } catch (ConnectionException $e) {
            Log::error('OpenSky departures connection failed', ['error' => $e->getMessage()]);
            throw new PlaneException("Could not reach OpenSky: {$e->getMessage()}");
        }

        if ($response->status() === 404) {
            return [];
        }

        if ($response->failed()) {
            Log::warning('OpenSky departures request failed', ['status' => $response->status()]);
            throw new PlaneException("OpenSky error: HTTP {$response->status()}");
        }

        return collect($response->json() ?? [])
            ->filter(fn(array $f) => $this->looksCommercial($f['callsign'] ?? null))
            ->map(fn(array $f) => [
                'icao24'           => $f['icao24'],
                'callsign'         => trim($f['callsign'] ?? ''),
                'airline'          => AirplaneNames::nameFor($f['callsign'] ?? null),
                'departureAirport' => AirportNames::cityFor($f['estDepartureAirport']),
                'arrivalAirport'   => AirportNames::cityFor($f['estArrivalAirport']),
                'departedAt'       => isset($f['firstSeen'])
                    ? Carbon::createFromTimestamp($f['firstSeen'], 'UTC')->toDateTimeString() . ' UTC'
                    : null,
            ])
            ->sortByDesc('departedAt')
            ->values()
            ->all();
    }

    public function track(string $icao24, int $time = 0): array
    {
        $request = Http::timeout($this->timeout);
        if ($this->clientId && $this->clientSecret) {
            $request = $request->withToken($this->accessToken());
        }

        try {
            $response = $request->get("{$this->baseUrl}/tracks/all", [
                'icao24' => strtolower($icao24),
                'time'   => $time,
            ]);
        } catch (ConnectionException $e) {
            Log::error('OpenSky track connection failed', ['error' => $e->getMessage()]);
            throw new PlaneException("Could not reach OpenSky: {$e->getMessage()}");
        }

        if ($response->status() === 404) {
            throw new PlaneException("No track found for aircraft {$icao24}.");
        }

        if ($response->failed()) {
            Log::warning('OpenSky track request failed', ['status' => $response->status()]);
            throw new PlaneException("OpenSky error: HTTP {$response->status()}");
        }

        $data = $response->json();

        return [
            'icao24'    => $data['icao24'] ?? $icao24,
            'callsign'  => isset($data['callsign']) ? trim($data['callsign']) : null,
            'startTime' => isset($data['startTime']) ? Carbon::createFromTimestamp($data['startTime'], 'UTC')->toDateTimeString() . ' UTC' : null,
            'endTime'   => isset($data['endTime']) ? Carbon::createFromTimestamp($data['endTime'], 'UTC')->toDateTimeString() . ' UTC' : null,
            'waypoints' => array_map(fn(array $w) => [
                'time'      => $w[0],
                'latitude'  => $w[1],
                'longitude' => $w[2],
                'altitude'  => $w[3],
                'track'     => $w[4],
                'onGround'  => $w[5],
            ], $data['path'] ?? []),
        ];
    }
}
