<?php

namespace App\Services\OpenSky;

/* Lets users search with city names instead of memorizing codes (ie ATH) */

class AirportNames
{
    private const AIRPORTS = [
        // Greece
        ['icao' => 'LGAV', 'iata' => 'ATH', 'name' => 'Athens International Airport', 'city' => 'Athens'],
        ['icao' => 'LGTS', 'iata' => 'SKG', 'name' => 'Thessaloniki Airport', 'city' => 'Thessaloniki'],
        ['icao' => 'LGIR', 'iata' => 'HER', 'name' => 'Heraklion International Airport', 'city' => 'Heraklion'],
        ['icao' => 'LGRP', 'iata' => 'RHO', 'name' => 'Rhodes International Airport', 'city' => 'Rhodes'],
        ['icao' => 'LGKR', 'iata' => 'CFU', 'name' => 'Corfu International Airport', 'city' => 'Corfu'],
        ['icao' => 'LGSR', 'iata' => 'JTR', 'name' => 'Santorini International Airport', 'city' => 'Thira'],
        ['icao' => 'LGBL', 'iata' => 'VOL', 'name' => 'Nea Anchialos National Airport', 'city' => 'Volos'],
        ['icao' => 'LGIO', 'iata' => 'IOA', 'name' => 'Ioannina National Airport "King Pyrrhus"', 'city' => 'Ioannina'],
        ['icao' => 'LGKL', 'iata' => 'KLX', 'name' => 'Kalamata International Airport', 'city' => 'Kalamata'],
        ['icao' => 'LGPZ', 'iata' => 'PVK', 'name' => 'Aktion National Airport', 'city' => 'Preveza / Lefkada'],
        ['icao' => 'LGKV', 'iata' => 'KVA', 'name' => 'Kavala International Airport', 'city' => 'Kavala'],
        ['icao' => 'LGAL', 'iata' => 'AXD', 'name' => 'Alexandroupoli Aristotelis Airport', 'city' => 'Alexandroupoli'],
        ['icao' => 'LGCH', 'iata' => 'CHQ', 'name' => 'Chania International Airport', 'city' => 'Chania'],
        ['icao' => 'LGMK', 'iata' => 'JMK', 'name' => 'Mykonos International Airport', 'city' => 'Mykonos'],
        ['icao' => 'LGNX', 'iata' => 'JNX', 'name' => 'Naxos Island National Airport', 'city' => 'Naxos'],
        ['icao' => 'LGPA', 'iata' => 'PAS', 'name' => 'Paros National Airport', 'city' => 'Paros'],
        ['icao' => 'LGML', 'iata' => 'MLO', 'name' => 'Milos Island National Airport', 'city' => 'Milos'],
        ['icao' => 'LGSY', 'iata' => 'JSY', 'name' => 'Syros Island National Airport', 'city' => 'Syros'],
        ['icao' => 'LGST', 'iata' => 'JSH', 'name' => 'Sitia Public Airport', 'city' => 'Sitia'],
        ['icao' => 'LGKF', 'iata' => 'EFL', 'name' => 'Kefalonia International Airport', 'city' => 'Kefalonia'],
        ['icao' => 'LGZA', 'iata' => 'ZTH', 'name' => 'Zakynthos International Airport', 'city' => 'Zakynthos'],
        ['icao' => 'LGKP', 'iata' => 'KGS', 'name' => 'Kos International Airport', 'city' => 'Kos'],
        ['icao' => 'LGMH', 'iata' => 'MJT', 'name' => 'Mytilene International Airport', 'city' => 'Lesbos'],
        ['icao' => 'LGSM', 'iata' => 'SMI', 'name' => 'Samos International Airport', 'city' => 'Samos'],
        ['icao' => 'LGHI', 'iata' => 'JKH', 'name' => 'Chios Island National Airport', 'city' => 'Chios'],
        ['icao' => 'LGLM', 'iata' => 'LXS', 'name' => 'Lemnos International Airport', 'city' => 'Lemnos'],
        ['icao' => 'LGKJ', 'iata' => 'AOK', 'name' => 'Karpathos Island National Airport', 'city' => 'Karpathos'],
        ['icao' => 'LGSK', 'iata' => 'JSI', 'name' => 'Skiathos International Airport', 'city' => 'Skiathos'],

        // Rest of Europe
        ['icao' => 'EGLL', 'iata' => 'LHR', 'name' => 'London Heathrow Airport', 'city' => 'London'],
        ['icao' => 'EGKK', 'iata' => 'LGW', 'name' => 'London Gatwick Airport', 'city' => 'London'],
        ['icao' => 'EGCC', 'iata' => 'MAN', 'name' => 'Manchester Airport', 'city' => 'Manchester'],
        ['icao' => 'EIDW', 'iata' => 'DUB', 'name' => 'Dublin Airport', 'city' => 'Dublin'],
        ['icao' => 'LFPG', 'iata' => 'CDG', 'name' => 'Paris Charles de Gaulle Airport', 'city' => 'Paris'],
        ['icao' => 'LFPO', 'iata' => 'ORY', 'name' => 'Paris Orly Airport', 'city' => 'Paris'],
        ['icao' => 'EDDF', 'iata' => 'FRA', 'name' => 'Frankfurt Airport', 'city' => 'Frankfurt'],
        ['icao' => 'EDDM', 'iata' => 'MUC', 'name' => 'Munich Airport', 'city' => 'Munich'],
        ['icao' => 'EDDB', 'iata' => 'BER', 'name' => 'Berlin Brandenburg Airport', 'city' => 'Berlin'],
        ['icao' => 'EHAM', 'iata' => 'AMS', 'name' => 'Amsterdam Airport Schiphol', 'city' => 'Amsterdam'],
        ['icao' => 'EBBR', 'iata' => 'BRU', 'name' => 'Brussels Airport', 'city' => 'Brussels'],
        ['icao' => 'LEMD', 'iata' => 'MAD', 'name' => 'Madrid–Barajas Airport', 'city' => 'Madrid'],
        ['icao' => 'LEBL', 'iata' => 'BCN', 'name' => 'Barcelona–El Prat Airport', 'city' => 'Barcelona'],
        ['icao' => 'LPPT', 'iata' => 'LIS', 'name' => 'Lisbon Airport', 'city' => 'Lisbon'],
        ['icao' => 'LIRF', 'iata' => 'FCO', 'name' => 'Rome Fiumicino Airport', 'city' => 'Rome'],
        ['icao' => 'LIMC', 'iata' => 'MXP', 'name' => 'Milan Malpensa Airport', 'city' => 'Milan'],
        ['icao' => 'LOWW', 'iata' => 'VIE', 'name' => 'Vienna International Airport', 'city' => 'Vienna'],
        ['icao' => 'LSZH', 'iata' => 'ZRH', 'name' => 'Zurich Airport', 'city' => 'Zurich'],
        ['icao' => 'LSGG', 'iata' => 'GVA', 'name' => 'Geneva Airport', 'city' => 'Geneva'],
        ['icao' => 'EKCH', 'iata' => 'CPH', 'name' => 'Copenhagen Airport', 'city' => 'Copenhagen'],
        ['icao' => 'ESSA', 'iata' => 'ARN', 'name' => 'Stockholm Arlanda Airport', 'city' => 'Stockholm'],
        ['icao' => 'ENGM', 'iata' => 'OSL', 'name' => 'Oslo Airport', 'city' => 'Oslo'],
        ['icao' => 'EFHK', 'iata' => 'HEL', 'name' => 'Helsinki Airport', 'city' => 'Helsinki'],
        ['icao' => 'BIKF', 'iata' => 'KEF', 'name' => 'Keflavík International Airport', 'city' => 'Reykjavík'],
        ['icao' => 'EPWA', 'iata' => 'WAW', 'name' => 'Warsaw Chopin Airport', 'city' => 'Warsaw'],
        ['icao' => 'LKPR', 'iata' => 'PRG', 'name' => 'Václav Havel Airport Prague', 'city' => 'Prague'],
        ['icao' => 'LHBP', 'iata' => 'BUD', 'name' => 'Budapest Ferenc Liszt International Airport', 'city' => 'Budapest'],
        ['icao' => 'LROP', 'iata' => 'OTP', 'name' => 'Henri Coandă International Airport', 'city' => 'Bucharest'],
        ['icao' => 'LBSF', 'iata' => 'SOF', 'name' => 'Sofia Airport', 'city' => 'Sofia'],
        ['icao' => 'UUEE', 'iata' => 'SVO', 'name' => 'Sheremetyevo International Airport', 'city' => 'Moscow'],
        ['icao' => 'LTFM', 'iata' => 'IST', 'name' => 'Istanbul Airport', 'city' => 'Istanbul'],

        // Middle East
        ['icao' => 'OMDB', 'iata' => 'DXB', 'name' => 'Dubai International Airport', 'city' => 'Dubai'],
        ['icao' => 'OMAA', 'iata' => 'AUH', 'name' => 'Abu Dhabi International Airport', 'city' => 'Abu Dhabi'],
        ['icao' => 'OTHH', 'iata' => 'DOH', 'name' => 'Hamad International Airport', 'city' => 'Doha'],
        ['icao' => 'OJAI', 'iata' => 'AMM', 'name' => 'Queen Alia International Airport', 'city' => 'Amman'],
        ['icao' => 'OERK', 'iata' => 'RUH', 'name' => 'King Khalid International Airport', 'city' => 'Riyadh'],
        ['icao' => 'OEJN', 'iata' => 'JED', 'name' => 'King Abdulaziz International Airport', 'city' => 'Jeddah'],
        ['icao' => 'LLBG', 'iata' => 'TLV', 'name' => 'Ben Gurion Airport', 'city' => 'Tel Aviv'],
        ['icao' => 'OKBK', 'iata' => 'KWI', 'name' => 'Kuwait International Airport', 'city' => 'Kuwait City'],
        ['icao' => 'OBBI', 'iata' => 'BAH', 'name' => 'Bahrain International Airport', 'city' => 'Manama'],

        // Asia
        ['icao' => 'RJAA', 'iata' => 'NRT', 'name' => 'Narita International Airport', 'city' => 'Tokyo'],
        ['icao' => 'RJTT', 'iata' => 'HND', 'name' => 'Haneda Airport', 'city' => 'Tokyo'],
        ['icao' => 'RJBB', 'iata' => 'KIX', 'name' => 'Kansai International Airport', 'city' => 'Osaka'],
        ['icao' => 'RKSI', 'iata' => 'ICN', 'name' => 'Incheon International Airport', 'city' => 'Seoul'],
        ['icao' => 'ZBAA', 'iata' => 'PEK', 'name' => 'Beijing Capital International Airport', 'city' => 'Beijing'],
        ['icao' => 'ZSPD', 'iata' => 'PVG', 'name' => 'Shanghai Pudong International Airport', 'city' => 'Shanghai'],
        ['icao' => 'ZGGG', 'iata' => 'CAN', 'name' => 'Guangzhou Baiyun International Airport', 'city' => 'Guangzhou'],
        ['icao' => 'VHHH', 'iata' => 'HKG', 'name' => 'Hong Kong International Airport', 'city' => 'Hong Kong'],
        ['icao' => 'RCTP', 'iata' => 'TPE', 'name' => 'Taiwan Taoyuan International Airport', 'city' => 'Taipei'],
        ['icao' => 'WSSS', 'iata' => 'SIN', 'name' => 'Singapore Changi Airport', 'city' => 'Singapore'],
        ['icao' => 'WMKK', 'iata' => 'KUL', 'name' => 'Kuala Lumpur International Airport', 'city' => 'Kuala Lumpur'],
        ['icao' => 'VTBS', 'iata' => 'BKK', 'name' => 'Suvarnabhumi Airport', 'city' => 'Bangkok'],
        ['icao' => 'VVNB', 'iata' => 'HAN', 'name' => 'Noi Bai International Airport', 'city' => 'Hanoi'],
        ['icao' => 'VVTS', 'iata' => 'SGN', 'name' => 'Tan Son Nhat International Airport', 'city' => 'Ho Chi Minh City'],
        ['icao' => 'RPLL', 'iata' => 'MNL', 'name' => 'Ninoy Aquino International Airport', 'city' => 'Manila'],
        ['icao' => 'VIDP', 'iata' => 'DEL', 'name' => 'Indira Gandhi International Airport', 'city' => 'Delhi'],
        ['icao' => 'VABB', 'iata' => 'BOM', 'name' => 'Chhatrapati Shivaji Maharaj International Airport', 'city' => 'Mumbai'],
        ['icao' => 'OPKC', 'iata' => 'KHI', 'name' => 'Jinnah International Airport', 'city' => 'Karachi'],

        // Oceania
        ['icao' => 'YSSY', 'iata' => 'SYD', 'name' => 'Sydney Kingsford Smith Airport', 'city' => 'Sydney'],
        ['icao' => 'YMML', 'iata' => 'MEL', 'name' => 'Melbourne Airport', 'city' => 'Melbourne'],
        ['icao' => 'YBBN', 'iata' => 'BNE', 'name' => 'Brisbane Airport', 'city' => 'Brisbane'],
        ['icao' => 'YPPH', 'iata' => 'PER', 'name' => 'Perth Airport', 'city' => 'Perth'],
        ['icao' => 'NZAA', 'iata' => 'AKL', 'name' => 'Auckland Airport', 'city' => 'Auckland'],

        // North America
        ['icao' => 'KJFK', 'iata' => 'JFK', 'name' => 'John F. Kennedy International Airport', 'city' => 'New York'],
        ['icao' => 'KLAX', 'iata' => 'LAX', 'name' => 'Los Angeles International Airport', 'city' => 'Los Angeles'],
        ['icao' => 'KORD', 'iata' => 'ORD', 'name' => "O'Hare International Airport", 'city' => 'Chicago'],
        ['icao' => 'KATL', 'iata' => 'ATL', 'name' => 'Hartsfield–Jackson Atlanta International Airport', 'city' => 'Atlanta'],
        ['icao' => 'KDFW', 'iata' => 'DFW', 'name' => 'Dallas/Fort Worth International Airport', 'city' => 'Dallas'],
        ['icao' => 'KSFO', 'iata' => 'SFO', 'name' => 'San Francisco International Airport', 'city' => 'San Francisco'],
        ['icao' => 'KMIA', 'iata' => 'MIA', 'name' => 'Miami International Airport', 'city' => 'Miami'],
        ['icao' => 'KSEA', 'iata' => 'SEA', 'name' => 'Seattle–Tacoma International Airport', 'city' => 'Seattle'],
        ['icao' => 'KBOS', 'iata' => 'BOS', 'name' => 'Logan International Airport', 'city' => 'Boston'],
        ['icao' => 'KIAD', 'iata' => 'IAD', 'name' => 'Washington Dulles International Airport', 'city' => 'Washington, D.C.'],
        ['icao' => 'CYYZ', 'iata' => 'YYZ', 'name' => 'Toronto Pearson International Airport', 'city' => 'Toronto'],
        ['icao' => 'CYVR', 'iata' => 'YVR', 'name' => 'Vancouver International Airport', 'city' => 'Vancouver'],
        ['icao' => 'CYUL', 'iata' => 'YUL', 'name' => 'Montréal–Trudeau International Airport', 'city' => 'Montreal'],
        ['icao' => 'MMMX', 'iata' => 'MEX', 'name' => 'Mexico City International Airport', 'city' => 'Mexico City'],

        // South America
        ['icao' => 'SBGR', 'iata' => 'GRU', 'name' => 'São Paulo–Guarulhos International Airport', 'city' => 'São Paulo'],
        ['icao' => 'SBGL', 'iata' => 'GIG', 'name' => 'Rio de Janeiro–Galeão International Airport', 'city' => 'Rio de Janeiro'],
        ['icao' => 'SAEZ', 'iata' => 'EZE', 'name' => 'Ministro Pistarini International Airport', 'city' => 'Buenos Aires'],
        ['icao' => 'SCEL', 'iata' => 'SCL', 'name' => 'Arturo Merino Benítez International Airport', 'city' => 'Santiago'],
        ['icao' => 'SKBO', 'iata' => 'BOG', 'name' => 'El Dorado International Airport', 'city' => 'Bogotá'],
        ['icao' => 'SPJC', 'iata' => 'LIM', 'name' => 'Jorge Chávez International Airport', 'city' => 'Lima'],

        // Africa
        ['icao' => 'HECA', 'iata' => 'CAI', 'name' => 'Cairo International Airport', 'city' => 'Cairo'],
        ['icao' => 'FAOR', 'iata' => 'JNB', 'name' => 'O.R. Tambo International Airport', 'city' => 'Johannesburg'],
        ['icao' => 'FACT', 'iata' => 'CPT', 'name' => 'Cape Town International Airport', 'city' => 'Cape Town'],
        ['icao' => 'GMMN', 'iata' => 'CMN', 'name' => 'Mohammed V International Airport', 'city' => 'Casablanca'],
        ['icao' => 'DNMM', 'iata' => 'LOS', 'name' => 'Murtala Muhammed International Airport', 'city' => 'Lagos'],
        ['icao' => 'HKJK', 'iata' => 'NBO', 'name' => 'Jomo Kenyatta International Airport', 'city' => 'Nairobi'],
        ['icao' => 'HAAB', 'iata' => 'ADD', 'name' => 'Bole International Airport', 'city' => 'Addis Ababa'],
    ];

    /** @return array<int, array{icao: string, iata: string, name: string, city: string}> */
    public static function resolve(string $query): array
    {
        $query = trim($query);
        if ($query === '') {
            return [];
        }

        // Typed an ICAO code directly — skip fuzzy matching entirely.
        if (preg_match('/^[A-Za-z]{4}$/', $query)) {
            $icao = strtoupper($query);
            foreach (self::AIRPORTS as $airport) {
                if ($airport['icao'] === $icao) {
                    return [$airport];
                }
            }
            return [['icao' => $icao, 'iata' => '', 'name' => $icao, 'city' => $icao]];
        }

        // Typed an IATA code directly (3 letters) — same reasoning: prefer an
        // exact match over fuzzy substring search, so e.g. "ATH" doesn't also
        // pull in unrelated airports whose city merely CONTAINS "ath" as a
        // substring (Karpathos being the actual real-world example of this).
        if (preg_match('/^[A-Za-z]{3}$/', $query)) {
            $iata = strtoupper($query);
            foreach (self::AIRPORTS as $airport) {
                if ($airport['iata'] === $iata) {
                    return [$airport];
                }
            }
            // Not a known IATA code — fall through, it might be a genuine
            // name/city fragment (e.g. someone typing a partial word).
        }

        $needle = mb_strtolower($query);

        return array_values(array_filter(
            self::AIRPORTS,
            fn(array $a) =>
            str_contains(mb_strtolower($a['name']), $needle)
                || str_contains(mb_strtolower($a['city']), $needle)
                || str_contains(mb_strtolower($a['iata']), $needle)
        ));
    }

    /** Looks up a city by ICAO code — falls back to the code itself if it's not in our list. */
    public static function cityFor(?string $icao): ?string
    {
        if (!$icao) {
            return null;
        }

        $icao = strtoupper($icao);

        foreach (self::AIRPORTS as $airport) {
            if ($airport['icao'] === $icao) {
                return $airport['city'];
            }
        }

        return $icao;
    }
}
