<?php

use CodeTech\EuPago\Models\MbReference;
use Illuminate\Support\Carbon;

it('scopes a query to paid references only', function () {
    createPendingMbReference(['reference' => '111', 'state' => 0]);
    createPendingMbReference(['reference' => '222', 'state' => 1]);

    expect(MbReference::paid()->count())->toBe(1);
});

it('casts the date columns to Carbon instances', function () {
    $reference = createPendingMbReference()->fresh();

    expect($reference->start_date)->toBeInstanceOf(Carbon::class)
        ->and($reference->end_date)->toBeInstanceOf(Carbon::class);
});
