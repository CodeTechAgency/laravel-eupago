<?php

use CodeTech\EuPago\Events\MBReferencePaid;
use Illuminate\Support\Facades\Event;

it('marks a pending MB reference as paid and dispatches the event', function () {
    Event::fake([MBReferencePaid::class]);
    $reference = createPendingMbReference();

    $response = $this->getJson(route('eupago.mb.callback', validMbCallbackPayload()));

    $response->assertOk()->assertJson(['response' => 'Success']);
    expect((int) $reference->fresh()->state)->toBe(1);
    Event::assertDispatched(
        MBReferencePaid::class,
        fn (MBReferencePaid $event) => $event->reference->is($reference)
    );
});

it('returns 404 when no pending MB reference matches', function () {
    Event::fake([MBReferencePaid::class]);

    // Note: the `exists` validation is currently dead (the `refrencia` typo bug),
    // so an unknown reference passes validation and 404s at the controller.
    $response = $this->getJson(route('eupago.mb.callback', validMbCallbackPayload([
        'referencia' => '000000000',
    ])));

    $response->assertNotFound()->assertJson(['response' => 'No pending reference found']);
    Event::assertNotDispatched(MBReferencePaid::class);
});

it('returns 404 when the matching MB reference is already paid', function () {
    createPendingMbReference(['state' => 1]);

    $response = $this->getJson(route('eupago.mb.callback', validMbCallbackPayload()));

    $response->assertNotFound();
});

it('rejects an MB callback from an unknown channel', function () {
    $response = $this->getJson(route('eupago.mb.callback', validMbCallbackPayload([
        'canal' => 'someone-else',
    ])));

    $response->assertStatus(422)->assertJsonStructure(['canal']);
});

it('rejects an MB callback with an invalid api key', function () {
    $response = $this->getJson(route('eupago.mb.callback', validMbCallbackPayload([
        'chave_api' => 'wrong-key',
    ])));

    $response->assertStatus(422)->assertJsonStructure(['chave_api']);
});

it('rejects an MB callback missing required fields', function () {
    $response = $this->getJson(route('eupago.mb.callback', ['valor' => '10.50']));

    $response->assertStatus(422);
});
