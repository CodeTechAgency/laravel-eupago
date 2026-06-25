<?php

use CodeTech\EuPago\Events\MBWayReferencePaid;
use Illuminate\Support\Facades\Event;

it('marks a pending MBWay reference as paid and dispatches the event', function () {
    Event::fake([MBWayReferencePaid::class]);
    $reference = createPendingMbwayReference();

    $response = $this->getJson(route('eupago.mbway.callback', validMbwayCallbackPayload()));

    $response->assertOk()->assertJson(['response' => 'Success']);
    expect((int) $reference->fresh()->state)->toBe(1);
    Event::assertDispatched(
        MBWayReferencePaid::class,
        fn (MBWayReferencePaid $event) => $event->reference->is($reference)
    );
});

it('returns 404 when the reference exists but the value does not match', function () {
    Event::fake([MBWayReferencePaid::class]);
    createPendingMbwayReference();

    $response = $this->getJson(route('eupago.mbway.callback', validMbwayCallbackPayload([
        'valor' => '99.99',
    ])));

    $response->assertNotFound()->assertJson(['response' => 'No pending reference found']);
    Event::assertNotDispatched(MBWayReferencePaid::class);
});

it('returns 404 when the matching MBWay reference is already paid', function () {
    createPendingMbwayReference(['state' => 1]);

    $response = $this->getJson(route('eupago.mbway.callback', validMbwayCallbackPayload()));

    $response->assertNotFound();
});

it('rejects an MBWay callback from an unknown channel', function () {
    $response = $this->getJson(route('eupago.mbway.callback', validMbwayCallbackPayload([
        'canal' => 'someone-else',
    ])));

    $response->assertStatus(422)->assertJsonStructure(['canal']);
});

it('rejects an MBWay callback with an invalid api key', function () {
    $response = $this->getJson(route('eupago.mbway.callback', validMbwayCallbackPayload([
        'chave_api' => 'wrong-key',
    ])));

    $response->assertStatus(422)->assertJsonStructure(['chave_api']);
});

it('rejects an MBWay callback missing required fields', function () {
    $response = $this->getJson(route('eupago.mbway.callback', ['valor' => '15.00']));

    $response->assertStatus(422);
});
