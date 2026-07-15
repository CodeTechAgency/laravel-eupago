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
    | OAuth 2.0 client credentials
    |--------------------------------------------------------------------------
    |
    | Eupago's management API (refunds, ...) is authenticated with OAuth 2.0
    | bearer tokens instead of the API key. Generate the client credentials
    | in the Eupago backoffice. Reference creation and status queries keep
    | using the API key, so these are only required for management calls.
    |
    */

    'client_id' => env('EUPAGO_CLIENT_ID'),

    'client_secret' => env('EUPAGO_CLIENT_SECRET'),

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
