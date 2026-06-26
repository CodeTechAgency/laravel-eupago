<?php

use CodeTech\EuPago\Models\PayShopReference;

it('scopes a query to paid references only', function () {
    createPendingPayShopReference(['reference' => 111, 'state' => 0]);
    createPendingPayShopReference(['reference' => 222, 'state' => 1]);

    expect(PayShopReference::paid()->count())->toBe(1);
});

it('casts value to float and state to integer', function () {
    $reference = createPendingPayShopReference(['value' => 12, 'state' => 1])->fresh();

    expect($reference->value)->toBeFloat()->toBe(12.0)
        ->and($reference->state)->toBeInt()->toBe(1);
});
