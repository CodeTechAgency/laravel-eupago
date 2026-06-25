<?php

use CodeTech\EuPago\Models\MbwayReference;

it('scopes a query to paid references only', function () {
    createPendingMbwayReference(['reference' => 111, 'state' => 0]);
    createPendingMbwayReference(['reference' => 222, 'state' => 1]);

    expect(MbwayReference::paid()->count())->toBe(1);
});

it('casts value to float and state to integer', function () {
    $reference = createPendingMbwayReference(['value' => 12, 'state' => 1])->fresh();

    expect($reference->value)->toBeFloat()->toBe(12.0)
        ->and($reference->state)->toBeInt()->toBe(1);
});
