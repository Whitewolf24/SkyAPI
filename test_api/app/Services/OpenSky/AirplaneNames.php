<?php

namespace App\Services\OpenSky;

/**
 * Maps a callsign's ICAO airline prefix (first 3 letters, e.g. "SEH" in
 * "SEH123") to a readable airline name — OpenSky only ever returns the
 * raw callsign, never the airline itself.
 */
class AirplaneNames
{
    private const AIRLINES = [
        // Greece
        'SEH' => 'SKY express',
        'AEE' => 'Aegean Airlines',
        'OAL' => 'Olympic Air',

        // Europe — low-cost & flag carriers
        'RYR' => 'Ryanair',
        'EZY' => 'easyJet',
        'WZZ' => 'Wizz Air',
        'VOE' => 'Volotea',
        'NAX' => 'Norwegian Air Shuttle',
        'PGT' => 'Pegasus Airlines',
        'SXS' => 'SunExpress',
        'DLH' => 'Lufthansa',
        'BAW' => 'British Airways',
        'AFR' => 'Air France',
        'KLM' => 'KLM',
        'IBE' => 'Iberia',
        'ITY' => 'ITA Airways',
        'SWR' => 'Swiss International Air Lines',
        'AUA' => 'Austrian Airlines',
        'BEL' => 'Brussels Airlines',
        'FIN' => 'Finnair',
        'SAS' => 'Scandinavian Airlines',
        'TAP' => 'TAP Air Portugal',
        'LOT' => 'LOT Polish Airlines',
        'ROT' => 'TAROM',

        // Turkey / Middle East
        'THY' => 'Turkish Airlines',
        'UAE' => 'Emirates',
        'QTR' => 'Qatar Airways',
        'ETD' => 'Etihad Airways',
        'SVA' => 'Saudia',
        'GFA' => 'Gulf Air',
        'MEA' => 'Middle East Airlines',
        'ELY' => 'El Al',

        // Africa
        'MSR' => 'EgyptAir',
        'ETH' => 'Ethiopian Airlines',
        'SAA' => 'South African Airways',
        'KQA' => 'Kenya Airways',
        'RAM' => 'Royal Air Maroc',

        // North America
        'AAL' => 'American Airlines',
        'DAL' => 'Delta Air Lines',
        'UAL' => 'United Airlines',
        'SWA' => 'Southwest Airlines',
        'JBU' => 'JetBlue Airways',
        'ACA' => 'Air Canada',
        'WJA' => 'WestJet',
        'AMX' => 'Aeroméxico',

        // South America
        'LAN' => 'LATAM Airlines',
        'TAM' => 'LATAM Brasil',
        'ARG' => 'Aerolíneas Argentinas',
        'AVA' => 'Avianca',
        'CMP' => 'Copa Airlines',

        // Asia
        'SIA' => 'Singapore Airlines',
        'CPA' => 'Cathay Pacific',
        'ANA' => 'All Nippon Airways',
        'JAL' => 'Japan Airlines',
        'KAL' => 'Korean Air',
        'AAR' => 'Asiana Airlines',
        'CCA' => 'Air China',
        'CES' => 'China Eastern Airlines',
        'CSN' => 'China Southern Airlines',
        'MAS' => 'Malaysia Airlines',
        'THA' => 'Thai Airways',
        'GIA' => 'Garuda Indonesia',
        'PAL' => 'Philippine Airlines',
        'AIC' => 'Air India',
        'IGO' => 'IndiGo',

        // Oceania
        'QFA' => 'Qantas',
        'ANZ' => 'Air New Zealand',
        'JST' => 'Jetstar Airways',
        'VIR' => 'Virgin Atlantic',
    ];

    public static function nameFor(?string $callsign): ?string
    {
        if (!$callsign) {
            return null;
        }

        $prefix = strtoupper(substr(trim($callsign), 0, 3));

        return self::AIRLINES[$prefix] ?? null;
    }
}
