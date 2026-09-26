<?php

use CodeTech\EuPago\Enums\PaymentMethod;
use CodeTech\EuPago\Http\Controllers\CallbackController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| EuPago Routes
|--------------------------------------------------------------------------
*/

Route::get('callback', [CallbackController::class, 'callback'])->name('callback');

/*
| Deprecated, to be removed in v4. A channel takes a single notification URL,
| so these are aliases of the endpoint above, kept for the URLs already set
| in Eupago's backoffice. Each falls back to its own payment method when a
| notification's `mp` is not one the package knows.
*/

$aliases = [
    'mb' => PaymentMethod::Multibanco,
    'mbway' => PaymentMethod::MbWay,
    'payshop' => PaymentMethod::PayShop,
    'paysafecard' => PaymentMethod::PaysafeCard,
    'creditcard' => PaymentMethod::CreditCard,
];

foreach ($aliases as $prefix => $method) {
    Route::get("{$prefix}/callback", [CallbackController::class, 'callback'])
        ->defaults('default_payment_method', $method->value)
        ->name("{$prefix}.callback");
}
