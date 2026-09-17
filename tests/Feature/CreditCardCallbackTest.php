<?php

use CodeTech\EuPago\Events\CreditCardReferencePaid;
use Illuminate\Support\Facades\Event;

it('marks a pending Credit Card reference as paid and dispatches the event', function () {
    Event::fake([CreditCardReferencePaid::class]);
    $reference = createPendingCreditCardReference();

    $response = $this->getJson(route('eupago.creditcard.callback', validCreditCardCallbackPayload()));

    $response->assertOk()->assertJson(['response' => 'Success']);
    expect((int) $reference->fresh()->state)->toBe(1);
    // The callback's `transacao` is the id refunds are keyed by.
    expect($reference->fresh()->transaction_id)->toBe('29753077');
    Event::assertDispatched(
        CreditCardReferencePaid::class,
        fn (CreditCardReferencePaid $event) => $event->reference->is($reference)
    );
});

it('marks the reference whose identifier the callback echoes back', function () {
    Event::fake([CreditCardReferencePaid::class]);
    // Two pending references sharing a counter value and an amount: only the
    // echoed identifier tells them apart.
    $other = createPendingCreditCardReference(['identifier' => 'order-49']);
    $paid = createPendingCreditCardReference();

    $response = $this->getJson(route('eupago.creditcard.callback', validCreditCardCallbackPayload()));

    $response->assertOk();
    expect((int) $paid->fresh()->state)->toBe(1);
    expect((int) $other->fresh()->state)->toBe(0);
});

it('returns 404 when the reference exists but the value does not match', function () {
    Event::fake([CreditCardReferencePaid::class]);
    createPendingCreditCardReference();

    $response = $this->getJson(route('eupago.creditcard.callback', validCreditCardCallbackPayload([
        'valor' => '99.99',
    ])));

    $response->assertNotFound()->assertJson(['response' => 'No pending reference found']);
    Event::assertNotDispatched(CreditCardReferencePaid::class);
});

it('returns 404 when the matching Credit Card reference is already paid', function () {
    createPendingCreditCardReference(['state' => 1]);

    $response = $this->getJson(route('eupago.creditcard.callback', validCreditCardCallbackPayload()));

    $response->assertNotFound();
});

it('rejects a Credit Card callback for a reference that does not exist', function () {
    $response = $this->getJson(route('eupago.creditcard.callback', validCreditCardCallbackPayload([
        'referencia' => '99999',
    ])));

    $response->assertStatus(422)->assertJsonStructure(['referencia']);
});

it('rejects a Credit Card callback from an unknown channel', function () {
    createPendingCreditCardReference();

    $response = $this->getJson(route('eupago.creditcard.callback', validCreditCardCallbackPayload([
        'canal' => 'someone-else',
    ])));

    $response->assertStatus(422)->assertJsonStructure(['canal']);
});

it('rejects a Credit Card callback with an invalid api key', function () {
    createPendingCreditCardReference();

    $response = $this->getJson(route('eupago.creditcard.callback', validCreditCardCallbackPayload([
        'chave_api' => 'wrong-key',
    ])));

    $response->assertStatus(422)->assertJsonStructure(['chave_api']);
});

it('rejects a Credit Card callback missing required fields', function () {
    $response = $this->getJson(route('eupago.creditcard.callback', ['valor' => '30.00']));

    $response->assertStatus(422);
});
