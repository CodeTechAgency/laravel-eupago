<?php

use CodeTech\EuPago\Events\PaysafecardReferencePaid;
use Illuminate\Support\Facades\Event;

it('marks a pending Paysafecard reference as paid and dispatches the event', function () {
    Event::fake([PaysafecardReferencePaid::class]);
    $reference = createPendingPaysafecardReference();

    $response = $this->getJson(route('eupago.paysafecard.callback', validPaysafecardCallbackPayload()));

    $response->assertOk()->assertJson(['response' => 'Success']);
    expect((int) $reference->fresh()->state)->toBe(1);
    Event::assertDispatched(
        PaysafecardReferencePaid::class,
        fn (PaysafecardReferencePaid $event) => $event->reference->is($reference)
    );
});

it('returns 404 when the reference exists but the value does not match', function () {
    Event::fake([PaysafecardReferencePaid::class]);
    createPendingPaysafecardReference();

    $response = $this->getJson(route('eupago.paysafecard.callback', validPaysafecardCallbackPayload([
        'valor' => '99.99',
    ])));

    $response->assertNotFound()->assertJson(['response' => 'No pending reference found']);
    Event::assertNotDispatched(PaysafecardReferencePaid::class);
});

it('returns 404 when the matching Paysafecard reference is already paid', function () {
    createPendingPaysafecardReference(['state' => 1]);

    $response = $this->getJson(route('eupago.paysafecard.callback', validPaysafecardCallbackPayload()));

    $response->assertNotFound();
});

it('rejects a Paysafecard callback for a reference that does not exist', function () {
    $response = $this->getJson(route('eupago.paysafecard.callback', validPaysafecardCallbackPayload([
        'referencia' => '999999999',
    ])));

    $response->assertStatus(422)->assertJsonStructure(['referencia']);
});

it('rejects a Paysafecard callback from an unknown channel', function () {
    createPendingPaysafecardReference();

    $response = $this->getJson(route('eupago.paysafecard.callback', validPaysafecardCallbackPayload([
        'canal' => 'someone-else',
    ])));

    $response->assertStatus(422)->assertJsonStructure(['canal']);
});

it('rejects a Paysafecard callback with an invalid api key', function () {
    createPendingPaysafecardReference();

    $response = $this->getJson(route('eupago.paysafecard.callback', validPaysafecardCallbackPayload([
        'chave_api' => 'wrong-key',
    ])));

    $response->assertStatus(422)->assertJsonStructure(['chave_api']);
});

it('rejects a Paysafecard callback missing required fields', function () {
    $response = $this->getJson(route('eupago.paysafecard.callback', ['valor' => '25.00']));

    $response->assertStatus(422);
});
