<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AircraftController;
use App\Http\Controllers\Maps;

require __DIR__ . '/settings.php';

Route::redirect('/', '/aircraft');
Route::get('/aircraft', [AircraftController::class, 'index'])->name('aircraft.index');
Route::get('/aircraft/{icao24}/track', [Maps::class, 'show'])->name('aircraft.track');
