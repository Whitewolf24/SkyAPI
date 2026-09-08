import { Fragment, FormEvent, useEffect, useRef, useState } from 'react';
import { router } from '@inertiajs/react';
import L from 'leaflet';
import 'leaflet/dist/leaflet.css';

interface Flight {
    icao24: string;
    callsign: string;
    airline: string | null;
    departureAirport: string | null;
    arrivalAirport: string | null;
    landedAt?: string | null;
    departedAt?: string | null;
}

interface AirportMatch { icao: string; iata: string; name: string; city: string; }
interface Track {
    icao24: string;
    callsign: string | null;
    startTime: string | null;
    endTime: string | null;
    waypoints: { latitude: number | null; longitude: number | null }[];
}

interface Props {
    query: string;
    matches: AirportMatch[];
    airport: AirportMatch | null;
    arrivals: Flight[];
    departures: Flight[];
    arrivalsError: string | null;
    departuresError: string | null;
    error: string | null;
}

const FLIGHTS_PER_PAGE = 15;

function FlightTrackMap({ track }: { track: Track }) {
    const mapRef = useRef<HTMLDivElement>(null);

    useEffect(() => {
        if (!mapRef.current) return;
        const points: [number, number][] = track.waypoints
            .filter((w) => w.latitude !== null && w.longitude !== null)
            .map((w) => [w.latitude as number, w.longitude as number]);
        if (points.length === 0) return;

        const map = L.map(mapRef.current).fitBounds(points);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© OpenStreetMap contributors',
        }).addTo(map);
        L.polyline(points, { color: 'blue' }).addTo(map);
        L.circleMarker(points[0], { radius: 6, color: 'green' }).addTo(map).bindTooltip('Start');
        L.circleMarker(points[points.length - 1], { radius: 6, color: 'red' }).addTo(map).bindTooltip('End');

        return () => { map.remove(); };
    }, [track]);

    return (
        <div>
            <p>{track.startTime} → {track.endTime}</p>
            <div ref={mapRef} style={{ height: '400px', width: '100%' }} />
        </div>
    );
}

function FlightsTable({
    flights, type, expandedRow, trackData, trackLoading, trackError, onToggleTrack,
}: {
    flights: Flight[];
    type: 'arrivals' | 'departures';
    expandedRow: string | null;
    trackData: Record<string, Track>;
    trackLoading: string | null;
    trackError: Record<string, string>;
    onToggleTrack: (rowKey: string, icao24: string) => void;
}) {
    const [page, setPage] = useState(1);
    const totalPages = Math.ceil(flights.length / FLIGHTS_PER_PAGE);
    const start = (page - 1) * FLIGHTS_PER_PAGE;
    const pageFlights = flights.slice(start, start + FLIGHTS_PER_PAGE);

    if (flights.length === 0) return <p>No commercial {type} found.</p>;

    return (
        <>
            <table>
                <thead>
                    <tr><th>Flight</th><th>From</th><th>To</th><th>{type === 'departures' ? 'Departed' : 'Landed'}</th><th></th></tr>
                </thead>
                <tbody>
                    {pageFlights.map((f) => {
                        const rowKey = `${type}-${f.icao24}-${f.landedAt ?? f.departedAt}`;
                        const isExpanded = expandedRow === rowKey;
                        return (
                            <Fragment key={rowKey}>
                                <tr>
                                    {/* <td>{f.callsign || f.icao24}</td> */}
                                    <td>
                                        {f.callsign ? (
                                            f.airline && f.airline !== 'Unknown' ? (
                                                // Εμφάνιση της εταιρείας και από κάτω το callsign
                                                <div>
                                                    <strong>{f.airline}</strong>
                                                    <br />
                                                    <span>{f.callsign.trim()}</span>
                                                </div>
                                            ) : (
                                                f.callsign.trim()
                                            )
                                        ) : (
                                            f.airline && f.airline !== 'Unknown' ? f.airline : f.icao24
                                        )}
                                    </td>
                                    <td>{f.departureAirport ?? '—'}</td>
                                    <td>{f.arrivalAirport ?? 'Unknown'}</td>
                                    <td>{f.landedAt ?? f.departedAt ?? '—'}</td>
                                    <td>
                                        <button type="button" onClick={() => onToggleTrack(rowKey, f.callsign?.trim() || f.icao24)}>
                                            {isExpanded ? 'Hide track' : 'View track'}
                                        </button>
                                    </td>
                                </tr>
                                {isExpanded && (
                                    <tr>
                                        <td colSpan={5}>
                                            {trackLoading === rowKey && <p>Loading track…</p>}
                                            {trackError[rowKey] && <p style={{ color: 'red' }}>{trackError[rowKey]}</p>}
                                            {trackData[rowKey] && <FlightTrackMap track={trackData[rowKey]} />}
                                        </td>
                                    </tr>
                                )}
                            </Fragment>
                        );
                    })}
                </tbody>
            </table>

            {
                totalPages > 1 && (
                    <div>
                        <button type="button" onClick={() => setPage((p) => Math.max(1, p - 1))} disabled={page === 1}>
                            Previous
                        </button>
                        {Array.from({ length: totalPages }, (_, i) => i + 1).map((n) => (
                            <button key={n} type="button" onClick={() => setPage(n)} disabled={n === page}>
                                {n}
                            </button>
                        ))}
                        <button type="button" onClick={() => setPage((p) => Math.min(totalPages, p + 1))} disabled={page === totalPages}>
                            Next
                        </button>
                    </div>
                )
            }
        </>
    );
}

export default function Index({ query, matches, airport, arrivals, departures, arrivalsError, departuresError, error }: Props) {
    const [search, setSearch] = useState(query);
    const [expandedRow, setExpandedRow] = useState<string | null>(null);
    const [trackData, setTrackData] = useState<Record<string, Track>>({});
    const [trackLoading, setTrackLoading] = useState<string | null>(null);
    const [trackError, setTrackError] = useState<Record<string, string>>({});

    const apiError = arrivalsError || departuresError;

    function handleSubmit(e: FormEvent) {
        e.preventDefault();
        router.get('/aircraft', { airport: search }, { preserveState: true });
    }

    async function handleToggleTrack(rowKey: string, icao24: string) {
        if (expandedRow === rowKey) {
            setExpandedRow(null);
            return;
        }
        setExpandedRow(rowKey);

        if (!trackData[rowKey]) {
            setTrackLoading(rowKey);
            try {
                const res = await fetch(`/aircraft/${icao24}/track`, { headers: { Accept: 'application/json' } });
                if (!res.ok) {
                    const body = await res.json().catch(() => null);
                    throw new Error(body?.error ?? `Request failed (${res.status})`);
                }
                const data = await res.json();
                setTrackData((prev) => ({ ...prev, [rowKey]: data }));
            } catch (err) {
                setTrackError((prev) => ({ ...prev, [rowKey]: err instanceof Error ? err.message : 'Failed to load track.' }));
            } finally {
                setTrackLoading(null);
            }
        }
    }

    return (
        <div>
            <h1>Commercial Traffic (OpenSky)</h1>
            <p>Shows yesterday's confirmed traffic — OpenSky processes this overnight, so today isn't available yet.</p>

            <form onSubmit={handleSubmit}>
                <label>
                    Airport
                    <input type="text" value={search} onChange={(e) => setSearch(e.target.value)} placeholder="e.g. Athens, ATH, or LGAV" />
                </label>
                <button type="submit">Search</button>
            </form>

            {error && <p style={{ color: 'red' }}>{error}</p>}

            {matches.length > 1 && (
                <div>
                    <p>Multiple airports match "{query}" — pick one:</p>
                    <ul>
                        {matches.map((m) => (
                            <li key={m.icao}>
                                <button type="button" onClick={() => router.get('/aircraft', { airport: m.icao })}>
                                    {m.name} ({m.iata || m.icao})
                                </button>
                            </li>
                        ))}
                    </ul>
                </div>
            )}

            {airport && (
                <>
                    {/* 1. Εμφάνιση του σφάλματος API ΜΟΝΟ ΜΙΑ ΦΟΡΑ στην κορυφή των αποτελεσμάτων */}
                    {apiError && <p style={{ color: 'red', fontWeight: 'bold', marginBottom: '20px' }}>{apiError}</p>}

                    <h2>Arrivals — {airport.name}</h2>
                    {/* 2. Αν υπάρχει σφάλμα API, κρύβουμε τον πίνακα τελείως */}
                    {!arrivalsError && (
                        <FlightsTable key={`arrivals-${airport.icao}`} flights={arrivals} type="arrivals" expandedRow={expandedRow} trackData={trackData} trackLoading={trackLoading} trackError={trackError} onToggleTrack={handleToggleTrack} />
                    )}

                    <h2>Departures — {airport.name}</h2>
                    {/* 3. Αν υπάρχει σφάλμα API, κρύβουμε τον πίνακα τελείως */}
                    {!departuresError && (
                        <FlightsTable key={`departures-${airport.icao}`} flights={departures} type="departures" expandedRow={expandedRow} trackData={trackData} trackLoading={trackLoading} trackError={trackError} onToggleTrack={handleToggleTrack} />)}
                </>
            )}
        </div>
    );
}
