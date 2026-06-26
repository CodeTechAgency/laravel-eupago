<?php

use CodeTech\EuPago\Events\PayShopReferencePaid;
use Illuminate\Support\Facades\Event;

it('marks a pending PayShop reference as paid and dispatches the event', function () {
    Event::fake([PayShopReferencePaid::class]);
    $reference = createPendingPayShopReference();

    $response = $this->getJson(route('eupago.payshop.callback', validPayShopCallbackPayload()));

    $response->assertOk()->assertJson(['response' => 'Success']);
    expect((int) $reference->fresh()->state)->toBe(1);
    Event::assertDispatched(
        PayShopReferencePaid::class,
        fn (PayShopReferencePaid $event) => $event->reference->is($reference)
    );
});

it('returns 404 when the reference exists but the value does not match', function () {
    Event::fake([PayShopReferencePaid::class]);
    createPendingPayShopReference();

    $response = $this->getJson(route('eupago.payshop.callback', validPayShopCallbackPayload([
        'valor' => '99.99',
    ])));

    $response->assertNotFound()->assertJson(['response' => 'No pending reference found']);
    Event::assertNotDispatched(PayShopReferencePaid::class);
});

it('returns 404 when the matching PayShop reference is already paid', function () {
    createPendingPayShopReference(['state' => 1]);

    $response = $this->getJson(route('eupago.payshop.callback', validPayShopCallbackPayload()));

    $response->assertNotFound();
});

it('rejects a PayShop callback for a reference that does not exist', function () {
    $response = $this->getJson(route('eupago.payshop.callback', validPayShopCallbackPayload([
        'referencia' => '111111111',
    ])));

    $response->assertStatus(422)->assertJsonStructure(['referencia']);
});

it('rejects a PayShop callback from an unknown channel', function () {
    $response = $this->getJson(route('eupago.payshop.callback', validPayShopCallbackPayload([
        'canal' => 'someone-else',
    ])));

    $response->assertStatus(422)->assertJsonStructure(['canal']);
});

it('rejects a PayShop callback with an invalid api key', function () {
    $response = $this->getJson(route('eupago.payshop.callback', validPayShopCallbackPayload([
        'chave_api' => 'wrong-key',
    ])));

    $response->assertStatus(422)->assertJsonStructure(['chave_api']);
});

it('rejects a PayShop callback missing required fields', function () {
    $response = $this->getJson(route('eupago.payshop.callback', ['valor' => '20.00']));

    $response->assertStatus(422);
});
