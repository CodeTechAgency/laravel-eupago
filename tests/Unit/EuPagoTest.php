<?php

use CodeTech\EuPago\EuPago;

function makeEuPago(): EuPago
{
    return new class extends EuPago {
        const URI = '/test';

        protected function getParams(): array
        {
            return [];
        }

        protected function mappedReferenceKeys(array $referenceData): array
        {
            return [];
        }
    };
}

it('uses the sandbox endpoint by default', function () {
    config()->set('eupago.env', 'test');

    expect(makeEuPago()->getBaseUri())->toBe(EuPago::TEST_ENDPOINT);
});

it('uses the production endpoint when the env is prod', function () {
    config()->set('eupago.env', 'prod');

    expect(makeEuPago()->getBaseUri())->toBe(EuPago::PROD_ENDPOINT);
});
