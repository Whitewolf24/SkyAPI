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
    arrivals_error: string | null;
    departures_error: string | null;
    error: string | null;
}

const FLIGHTS_PER_PAGE = 15;

function FMap({ track }: { track: Track }) {
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
    flights, type, expanded, map_data, map_loading, map_error, onToggleTrack,
}: {
    flights: Flight[];
    type: 'arrivals' | 'departures';
    expanded: string | null;
    map_data: Record<string, Track>;
    map_loading: string | null;
    map_error: Record<string, string>;
    onToggleTrack: (rowKey: string, icao24: string) => void;
}) {
    const [page, setPage] = useState(1);
    const totalPages = Math.ceil(flights.length / FLIGHTS_PER_PAGE);
    const start = (page - 1) * FLIGHTS_PER_PAGE;
    const pageFlights = flights.slice(start, start + FLIGHTS_PER_PAGE);

    if (flights.length === 0) return <p>No commercial {type} found.</p>;

    return (
        <>
            <div className="w-full overflow-hidden border border-slate-800 bg-slate-900 bg-opacity-40 backdrop-blur-md rounded-2xl shadow-xl">
                <table className="w-full text-left border-collapse">
                    <thead>
                        <tr className="border-b border-slate-800 bg-slate-950 bg-opacity-60 text-slate-400 font-mono text-xs uppercase tracking-wider">
                            <th className="pl-4 py-3.5 font-bold">Flight</th>
                            <th className="pl-2 py-3.5 font-bold">From</th>
                            <th className="pl-2 py-3.5 font-bold">To</th>
                            <th className="px-6 py-3.5 font-bold">{type === 'departures' ? 'Departed' : 'Landed'}</th>
                            <th className="pr-6 py-3.5 font-bold text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody className="divide-y divide-slate-800 text-sm font-medium text-slate-200">
                        {pageFlights.map((f) => {
                            const rowKey = `${type}-${f.icao24}-${f.landedAt ?? f.departedAt}`;
                            const isExpanded = expanded === rowKey;
                            return (
                                <Fragment key={rowKey}>
                                    <tr className="hover:bg-slate-800 hover:bg-opacity-30 transition-colors duration-150 text-center ">
                                        <td className="px-4 py-3.5">
                                            {f.callsign ? (
                                                f.airline && f.airline !== 'Unknown' ? (
                                                    <div className="flex flex-col">
                                                        <strong className="text-white font-bold tracking-wide">{f.airline}</strong>
                                                        <span className="text-xs font-mono text-blue-400 mt-0.5">{f.callsign.trim()}</span>
                                                    </div>
                                                ) : (
                                                    <span className="font-mono text-blue-400">{f.callsign.trim()}</span>
                                                )
                                            ) : (
                                                <span className="font-mono text-slate-400">
                                                    {f.airline && f.airline !== 'Unknown' ? f.airline : f.icao24}
                                                </span>
                                            )}
                                        </td>
                                        <td className="pl-2 py-3.5 text-slate-300 font-mono tracking-wide">{f.departureAirport ?? '—'}</td>
                                        <td className="pl-4 py-3.5 text-slate-300 font-mono tracking-wide">{f.arrivalAirport ?? 'Unknown'}</td>
                                        <td className="px-6  py-3.5 text-slate-400 text-xs font-mono">{f.landedAt ?? f.departedAt ?? '—'}</td>
                                        <td className="pr-4 py-3.5 text-right">
                                            <button
                                                type="button"
                                                onClick={() => onToggleTrack(rowKey, f.icao24)}
                                                className={`inline-flex items-center gap-2 px-3 py-1.5 text-xs font-bold rounded-xl border transition-all cursor-pointer shadow-sm ${isExpanded ? 'bg-blue-500 bg-opacity-10 border-blue-500 border-opacity-30 text-blue-400 hover:bg-opacity-20' : 'bg-slate-950 border-slate-800 text-slate-300 hover:border-slate-700 hover:text-white'}`}
                                            >
                                                {isExpanded && <span className="h-1.5 w-1.5 rounded-full bg-blue-400 animate-pulse"></span>}
                                                {isExpanded ? 'Hide Route' : 'View Route'}
                                            </button>
                                        </td>
                                    </tr>
                                    {isExpanded && (
                                        <tr className="bg-slate-950 bg-opacity-40">
                                            <td colSpan={5} className="px-4 py-4 border-t border-slate-800">
                                                <div className="w-full bg-slate-950 bg-opacity-80 border border-slate-800 rounded-xl p-4 min-h-[160px] flex items-center justify-center relative">
                                                    {map_loading === rowKey && (
                                                        <div className="flex items-center gap-2 text-sm font-semibold text-slate-400 animate-pulse">
                                                            <svg className="animate-spin h-4 w-4 text-blue-400" xmlns="http://w3.org" fill="none" viewBox="0 0 24 24">
                                                                <circle className="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" strokeWidth="4"></circle>
                                                                <path className="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                                            </svg>
                                                            <span>Loading flight path data…</span>
                                                        </div>
                                                    )}
                                                    {map_error[rowKey] && (
                                                        <p className="text-sm font-bold text-rose-400 bg-rose-500 bg-opacity-5 px-3 py-1.5 border border-rose-500 border-opacity-20 rounded-lg">⚠️ {map_error[rowKey]}</p>
                                                    )}
                                                    {map_data[rowKey] && (
                                                        <div className="w-full h-full rounded-lg overflow-hidden animate-fade-in">
                                                            <FMap track={map_data[rowKey]} />
                                                        </div>
                                                    )}
                                                </div>
                                            </td>
                                        </tr>
                                    )}
                                </Fragment>
                            );
                        })}
                    </tbody>
                </table>
            </div>

            {totalPages > 1 && (
                <div className="flex flex-wrap items-center justify-center gap-2 mt-6 w-full">
                    <button
                        type="button"
                        onClick={() => setPage((p) => Math.max(1, p - 1))}
                        disabled={page === 1}
                        className="px-3 py-1.5 text-xs font-bold rounded-xl bg-slate-900 border border-slate-800 text-slate-400 hover:text-white hover:border-slate-700 transition-all disabled:opacity-40 disabled:hover:text-slate-400 disabled:hover:border-slate-800 disabled:cursor-not-allowed cursor-pointer"
                    >
                        Prev
                    </button>
                    {Array.from({ length: totalPages }, (_, i) => i + 1).map((n) => (
                        <button
                            key={n}
                            type="button"
                            onClick={() => setPage(n)}
                            disabled={n === page}
                            className={`px-3 py-1.5 text-xs font-mono font-bold rounded-xl border transition-all cursor-pointer ${n === page ? 'bg-gradient-to-r from-blue-500 to-sky-500 border-blue-400 text-white shadow-md' : 'bg-slate-900 border-slate-800 text-slate-400 hover:text-white hover:border-slate-700'}`}
                        >
                            {n}
                        </button>
                    ))}
                    <button
                        type="button"
                        onClick={() => setPage((p) => Math.min(totalPages, p + 1))}
                        disabled={page === totalPages}
                        className="px-3 py-1.5 text-xs font-bold rounded-xl bg-slate-900 border border-slate-800 text-slate-400 hover:text-white hover:border-slate-700 transition-all disabled:opacity-40 disabled:hover:text-slate-400 disabled:hover:border-slate-800 disabled:cursor-not-allowed cursor-pointer"
                    >
                        Next
                    </button>
                </div>
            )}
        </>
    );
}

export default function Index({ query, matches, airport, arrivals, departures, arrivals_error, departures_error, error }: Props) {
    const [search, set_search] = useState(query);
    const [expanded, set_expanded] = useState<string | null>(null);
    const [map_data, setmap_data] = useState<Record<string, Track>>({});
    const [map_loading, setmap_loading] = useState<string | null>(null);
    const [map_error, setmap_error] = useState<Record<string, string>>({});
    const [loading, set_loading] = useState(false);

    const api_error = arrivals_error || departures_error;

    function handle_submit(e: FormEvent) {
        e.preventDefault();

        router.get(
            '/aircraft',
            { airport: search },
            {
                preserveState: true,

                onStart: () => {
                    set_loading(true);
                },

                onFinish: () => {
                    set_loading(false);
                },

                onError: (errors) => {
                    console.error("Request failed", errors);
                    set_loading(false);
                }
            }
        );

    }
    async function toggle_map(rowKey: string, icao24: string) {
        if (expanded === rowKey) {
            set_expanded(null);
            return;
        }
        set_expanded(rowKey);

        if (!map_data[rowKey]) {
            setmap_loading(rowKey);
            try {
                const res = await fetch(`/aircraft/${icao24}/track`, { headers: { Accept: 'application/json' } });
                if (!res.ok) {
                    const body = await res.json().catch(() => null);
                    throw new Error(body?.error ?? `Request failed (${res.status})`);
                }
                const data = await res.json();
                setmap_data((prev) => ({ ...prev, [rowKey]: data }));
            } catch (err) {
                setmap_error((prev) => ({ ...prev, [rowKey]: err instanceof Error ? err.message : 'Failed to load map data.' }));
            } finally {
                setmap_loading(null);
            }
        }
    }

    return (
        <div className="relative min-h-screen w-full bg-slate-950 text-slate-100 flex flex-col items-center p-6 overflow-hidden animate-fade-in">
            <div className="absolute top-1/4 left-1/2 -translate-x-1/2 -translate-y-1/2 w-125 h-125 bg-blue-500/10 rounded-full blur-[120px] z-0" />
            <div className="absolute top-1/3 left-1/4 w-125 h-75 bg-indigo-500/10 rounded-full blur-[100px] z-0" />

            <div className="relative z-10 flex flex-col items-center mt-20 mb-12">
                <a href="/"><h1 className="font-montserrat text-4xl font-black bg-linear-to-r from-blue-400 to-sky-300 bg-clip-text text-transparent tracking-wider z-10 cursor-default">
                    SkyAPI
                </h1> </a>

                <img
                    src="whiteplane.png"
                    alt="Airplane"
                    className="animate-fly w-48 h-auto opacity-40 object-contain brightness-110 drop-shadow-[0_0_15px_rgba(56,189,248,0.2)] -mt-2"
                />

                <p className="text-slate-400 text-sm text-center font-medium tracking-wide -mt-15 cursor-defaul">
                    See yesterday's confirmed traffic via OpenSky API
                </p>
            </div>

            <div className="relative z-10 w-full max-w-md bg-slate-900/50 backdrop-blur-md border border-slate-800 p-6 rounded-2xl shadow-xl shadow-blue-500/5">
                <form onSubmit={handle_submit} className="flex flex-col gap-4">
                    <div className="flex flex-col gap-1.5 text-left">
                        <label className="text-sm font-bold text-blue-300 tracking-wide ml-1">
                            Airport
                        </label>

                        <input
                            type="text"
                            value={search}
                            onChange={(e) => set_search(e.target.value.toUpperCase())}
                            placeholder="Athens or ATH"
                            className="w-full px-4 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-white placeholder-slate-500 focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 transition-all font-medium"
                        />
                    </div>

                    <button
                        type="submit"
                        className="w-full mt-2 bg-gradient-to-r from-blue-600 to-sky-500 hover:from-blue-600 hover:to-sky-800 text-white font-bold py-2.5 px-4 rounded-xl shadow-lg shadow-blue-500/20 active:scale-[0.98] transition-all cursor-pointer"
                    >

                        {loading ? (
                            <>
                                <div className="flex items-center justify-center">
                                    <svg className="animate-spin h-5 w-5 text-white" xmlns="http://w3.org" fill="none" viewBox="0 0 24 24">
                                        <circle className="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" strokeWidth="4"></circle>
                                        <path className="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                    <span className="ml-2">Searching...</span>
                                </div>
                            </>
                        ) : (
                            "Search Flights"
                        )}

                    </button>

                </form>
            </div>
            {error && <p className="col-span-1 lg:col-span-2 p-4 bg-rose-500/10 border border-rose-500/30 text-rose-400 font-bold text-sm rounded-xl text-center shadow-lg shadow-rose-500/5 mt-5">{error}</p>}

            {
                matches.length > 1 && (
                    <div className="animate-fade-in">
                        <p className="text-sm font-bold text-blue-300 tracking-wide mt-10">
                            Multiple airports match "{query}". Please choose one:
                        </p>
                        <ul className="text-sm font-bold tracking-wide text-center mt-5 space-y-2.5 ">
                            {matches.map((m) => (
                                <li key={m.icao} className="hover:bg-slate-700 transition-colors rounded-xl shadow-md shadow-blue-500/5">
                                    <button type="button" onClick={() => router.get('/aircraft', { airport: m.icao }, { preserveState: true })}>                                        {m.name} ({m.iata || m.icao})
                                    </button>
                                </li>
                            ))}
                        </ul>
                    </div>
                )
            }

            {
                airport && (
                    <div className="animate-fade-in w-full max-w-7xl mt-12 mb-20 relative z-10 grid grid-cols-1 lg:grid-cols-2 gap-6 items-start">

                        {/* API ERROR*/}
                        {api_error && (
                            <div className="col-span-1 lg:col-span-2 p-4 bg-rose-500/10 border border-rose-500/30 text-rose-400 font-bold text-sm rounded-xl text-center shadow-lg shadow-rose-500/5">
                                {api_error}
                            </div>
                        )}

                        {/* ARRIVALS SECTION */}
                        <div className="space-y-4 w-full bg-slate-500/10 backdrop-blur-sm p-5 border border-slate-900/60 rounded-2xl">
                            <div className="flex items-center gap-3 border-b border-slate-800/60 pb-3">
                                <svg className="w-6 h-6 text-emerald-400 transform rotate-45 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth="2.5">
                                    <path strokeLinecap="round" strokeLinejoin="round" d="M19 14l-7 7m0 0l-7-7m7 7V3" />
                                </svg>
                                <h2 className="font-montserrat text-xl font-extrabold text-slate-100 tracking-wide text-left">
                                    Arrivals at <span className="underline decoration-blue-400 decoration-2 underline-offset-4 text-blue-300">{airport.name}</span>
                                </h2>
                            </div>

                            {!arrivals_error ? (
                                <div className="overflow-x-auto w-full">
                                    <FlightsTable key={`arrivals-${airport.icao}`} flights={arrivals} type="arrivals" expanded={expanded} map_data={map_data} map_loading={map_loading} map_error={map_error} onToggleTrack={toggle_map} />
                                </div>
                            ) : (
                                <p className="text-sm font-medium text-slate-500 italic pl-1 text-left">No recent arrivals data found.</p>
                            )}
                        </div>

                        {/* DEPARTURES SECTION */}
                        <div className="space-y-4 w-full bg-slate-500/10 backdrop-blur-sm p-5 border border-slate-900/60 rounded-2xl">
                            <div className="flex items-center gap-3 border-b border-slate-800/60 pb-3">
                                <svg className="w-6 h-6 text-blue-400 transform -rotate-45 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth="2.5">
                                    <path strokeLinecap="round" strokeLinejoin="round" d="M5 10l7-7m0 0l7 7m-7-7v18" />
                                </svg>
                                <h2 className="font-montserrat text-xl font-extrabold text-slate-100 tracking-wide text-left">
                                    Departures from <span className="underline decoration-blue-400 decoration-2 underline-offset-4 text-blue-300">{airport.name}</span>
                                </h2>
                            </div>

                            {!departures_error ? (
                                <div className="overflow-x-auto w-full">
                                    <FlightsTable key={`departures-${airport.icao}`} flights={departures} type="departures" expanded={expanded} map_data={map_data} map_loading={map_loading} map_error={map_error} onToggleTrack={toggle_map} />
                                </div>
                            ) : (
                                <p className="text-sm font-medium text-slate-500 italic pl-1 text-left">No recent departures data found.</p>
                            )}
                        </div>

                    </div>
                )
            }
        </div >
    );
}
