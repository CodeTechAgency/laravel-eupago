<?php

use CodeTech\EuPago\EuPago;

it('uses the sandbox endpoint by default', function () {
    config()->set('eupago.env', 'test');

    expect((new EuPago)->getBaseUri())->toBe(EuPago::TEST_ENDPOINT);
});

it('uses the production endpoint when the env is prod', function () {
    config()->set('eupago.env', 'prod');

    expect((new EuPago)->getBaseUri())->toBe(EuPago::PROD_ENDPOINT);
});
