<?php

namespace CodeTech\EuPago;

class EuPago
{
    /**
     * The test endpoint
     */
    const TEST_ENDPOINT = 'https://sandbox.eupago.pt';

    /**
     * The production endpoint
     */
    const PROD_ENDPOINT = 'https://clientes.eupago.pt';

    /**
     * Returns the base uri, based on the current environment.
     *
     * @return string
     */
    public function getBaseUri()
    {
        return config('eupago.env') == 'prod' ? self::PROD_ENDPOINT : self::TEST_ENDPOINT;
    }
}
