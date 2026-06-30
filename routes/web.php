<?php

use CodeTech\EuPago\Http\Controllers\MBController;
use CodeTech\EuPago\Http\Controllers\MBWayController;
use CodeTech\EuPago\Http\Controllers\PaysafecardController;
use CodeTech\EuPago\Http\Controllers\PayShopController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| EuPago Routes
|--------------------------------------------------------------------------
*/

// MB
Route::prefix('mb')->name('mb.')->group(function () {
    Route::get('callback', [MBController::class, 'callback'])->name('callback');
});

// MB Way
Route::prefix('mbway')->name('mbway.')->group(function () {
    Route::get('callback', [MBWayController::class, 'callback'])->name('callback');
});

// PayShop
Route::prefix('payshop')->name('payshop.')->group(function () {
    Route::get('callback', [PayShopController::class, 'callback'])->name('callback');
});

// Paysafecard
Route::prefix('paysafecard')->name('paysafecard.')->group(function () {
    Route::get('callback', [PaysafecardController::class, 'callback'])->name('callback');
});
