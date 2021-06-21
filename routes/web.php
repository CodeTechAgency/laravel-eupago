<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| EuPago Routes
|--------------------------------------------------------------------------
*/


// MB
Route::prefix('mb')->name('mb.')->group(function () {
    Route::get('callback', 'MBController@callback')->name('callback');
});

// MB Way
Route::prefix('mbway')->name('mbway.')->group(function () {
    Route::get('callback', 'MBWayController@callback')->name('callback');
});
