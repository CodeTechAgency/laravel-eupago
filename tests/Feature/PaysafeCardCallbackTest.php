<?php

use CodeTech\EuPago\Events\PaysafeCardReferencePaid;
use Illuminate\Support\Facades\Event;

it('marks a pending PaysafeCard reference as paid and dispatches the event', function () {
    Event::fake([PaysafeCardReferencePaid::class]);
    $reference = createPendingPaysafeCardReference();

    $response = $this->getJson(route('eupago.paysafecard.callback', validPaysafeCardCallbackPayload()));

    $response->assertOk()->assertJson(['response' => 'Success']);
    expect((int) $reference->fresh()->state)->toBe(1);
    Event::assertDispatched(
        PaysafeCardReferencePaid::class,
        fn (PaysafeCardReferencePaid $event) => $event->reference->is($reference)
    );
});

it('returns 404 when the reference exists but the value does not match', function () {
    Event::fake([PaysafeCardReferencePaid::class]);
    createPendingPaysafeCardReference();

    $response = $this->getJson(route('eupago.paysafecard.callback', validPaysafeCardCallbackPayload([
        'valor' => '99.99',
    ])));

    $response->assertNotFound()->assertJson(['response' => 'No pending reference found']);
    Event::assertNotDispatched(PaysafeCardReferencePaid::class);
});

it('returns 404 when the matching PaysafeCard reference is already paid', function () {
    createPendingPaysafeCardReference(['state' => 1]);

    $response = $this->getJson(route('eupago.paysafecard.callback', validPaysafeCardCallbackPayload()));

    $response->assertNotFound();
});

it('rejects a PaysafeCard callback for a reference that does not exist', function () {
    $response = $this->getJson(route('eupago.paysafecard.callback', validPaysafeCardCallbackPayload([
        'referencia' => '999999999',
    ])));

    $response->assertStatus(422)->assertJsonStructure(['referencia']);
});

it('rejects a PaysafeCard callback from an unknown channel', function () {
    createPendingPaysafeCardReference();

    $response = $this->getJson(route('eupago.paysafecard.callback', validPaysafeCardCallbackPayload([
        'canal' => 'someone-else',
    ])));

    $response->assertStatus(422)->assertJsonStructure(['canal']);
});

it('rejects a PaysafeCard callback with an invalid api key', function () {
    createPendingPaysafeCardReference();

    $response = $this->getJson(route('eupago.paysafecard.callback', validPaysafeCardCallbackPayload([
        'chave_api' => 'wrong-key',
    ])));

    $response->assertStatus(422)->assertJsonStructure(['chave_api']);
});

it('rejects a PaysafeCard callback missing required fields', function () {
    $response = $this->getJson(route('eupago.paysafecard.callback', ['valor' => '25.00']));

    $response->assertStatus(422);
});
