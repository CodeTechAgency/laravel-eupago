<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Environment
    |--------------------------------------------------------------------------
    |
    | While you're developing your application, you might want to use the
    | sandbox environment. When your application is ready for production
    | switch to the production environment for making real payments.
    |
    | Environments:
    |   - test: https://sandbox.eupago.pt/clientes/rest_api
    |   - prod: https://clientes.eupago.pt/clientes/rest_api
    |
    */

    'env' => env('EUPAGO_ENV', 'test'),

    /*
    |--------------------------------------------------------------------------
    | API
    |--------------------------------------------------------------------------
    */

    'api_key' => env('EUPAGO_API_KEY'),

    /*
    |--------------------------------------------------------------------------
    | Channel
    |--------------------------------------------------------------------------
    */

    'channel' => env('EUPAGO_CHANNEL', 'demo'),

    /*
    |--------------------------------------------------------------------------
    | Routes
    |--------------------------------------------------------------------------
    |
    | The package automatically registers the payment callback routes
    | (e.g. /eupago/mb/callback). Disable this if you use the package as
    | a thin API client and handle EuPago's webhooks yourself.
    |
    */

    'routes' => (bool) env('EUPAGO_ROUTES', true),

];
