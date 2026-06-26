<?php

use CodeTech\EuPago\Events\PayShopReferencePaid;
use Illuminate\Support\Facades\Event;

it('dispatches the paid event via the static dispatch helper', function () {
    Event::fake([PayShopReferencePaid::class]);

    $reference = createPendingPayShopReference();
    PayShopReferencePaid::dispatch($reference);

    Event::assertDispatched(
        PayShopReferencePaid::class,
        fn (PayShopReferencePaid $event) => $event->reference->is($reference)
    );
});
