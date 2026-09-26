<?php

use CodeTech\EuPago\Events\CreditCardReferencePaid;
use CodeTech\EuPago\Events\MBReferencePaid;
use CodeTech\EuPago\Events\MBWayReferencePaid;
use CodeTech\EuPago\Events\PaysafeCardReferencePaid;
use CodeTech\EuPago\Events\PayShopReferencePaid;
use CodeTech\EuPago\Models\MbwayReference;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;

it('confirms every payment method on the single callback endpoint', function (string $mp, Closure $create, Closure $payload, string $event) {
    Event::fake([$event]);
    $reference = $create();

    $response = $this->getJson(route('eupago.callback', $payload(['mp' => $mp])));

    $response->assertOk()->assertJson(['response' => 'Success']);
    expect((int) $reference->fresh()->state)->toBe(1);
    Event::assertDispatched($event);
})->with([
    'Multibanco' => ['PC:PT', fn () => createPendingMbReference(), fn ($o) => validMbCallbackPayload($o), MBReferencePaid::class],
    'MB WAY' => ['MW:PT', fn () => createPendingMbwayReference(), fn ($o) => validMbwayCallbackPayload($o), MBWayReferencePaid::class],
    'PayShop' => ['PS:PT', fn () => createPendingPayShopReference(), fn ($o) => validPayShopCallbackPayload($o), PayShopReferencePaid::class],
    'PaysafeCard' => ['PF:PT', fn () => createPendingPaysafeCardReference(), fn ($o) => validPaysafeCardCallbackPayload($o), PaysafeCardReferencePaid::class],
    'Credit Card' => ['CC:PT', fn () => createPendingCreditCardReference(), fn ($o) => validCreditCardCallbackPayload($o), CreditCardReferencePaid::class],
]);

it('rejects a payment method the package does not handle', function () {
    $response = $this->getJson(route('eupago.callback', validMbCallbackPayload(['mp' => 'XX:PT'])));

    $response->assertStatus(422)->assertJsonStructure(['mp']);
});

it('rejects a payment method that is not a string', function () {
    $response = $this->getJson(route('eupago.callback', validMbCallbackPayload(['mp' => ['PC:PT']])));

    $response->assertStatus(422)->assertJsonStructure(['mp']);
});

it('checks the api key before the payment method', function () {
    $response = $this->getJson(route('eupago.callback', validMbCallbackPayload([
        'mp' => 'XX:PT',
        'chave_api' => 'wrong-key',
    ])));

    $response->assertStatus(422)->assertJsonStructure(['chave_api'])->assertJsonMissingPath('mp');
});

it('checks the api key and channel before looking up the reference', function () {
    $response = $this->getJson(route('eupago.mb.callback', validMbCallbackPayload([
        'referencia' => '000000000',
        'canal' => 'someone-else',
        'chave_api' => 'wrong-key',
    ])));

    $response->assertStatus(422)
        ->assertJsonStructure(['canal', 'chave_api'])
        ->assertJsonMissingPath('referencia');
});

it('handles a notification of another method on a deprecated per-method endpoint', function () {
    Event::fake([MBWayReferencePaid::class]);
    $reference = createPendingMbwayReference();

    $response = $this->getJson(route('eupago.mb.callback', validMbwayCallbackPayload()));

    $response->assertOk();
    expect((int) $reference->fresh()->state)->toBe(1);
    Event::assertDispatched(MBWayReferencePaid::class);
});

it('falls back to the method of a deprecated per-method endpoint for an unknown code', function () {
    Event::fake([MBWayReferencePaid::class]);
    $reference = createPendingMbwayReference();

    $response = $this->getJson(route('eupago.mbway.callback', validMbwayCallbackPayload(['mp' => 'MBWAY'])));

    $response->assertOk();
    expect((int) $reference->fresh()->state)->toBe(1);
});

it('does not start a session for a callback', function (string $route) {
    $middleware = app('router')->getRoutes()->getByName($route)->gatherMiddleware();

    expect($middleware)->toBe([]);
})->with(['eupago.callback', 'eupago.mb.callback', 'eupago.creditcard.callback']);

it('acknowledges a redelivered notification without firing the event again', function () {
    Event::fake([MBWayReferencePaid::class]);
    createPendingMbwayReference();

    $this->getJson(route('eupago.callback', validMbwayCallbackPayload()))->assertOk();
    $response = $this->getJson(route('eupago.callback', validMbwayCallbackPayload()));

    $response->assertOk();
    Event::assertDispatchedTimes(MBWayReferencePaid::class, 1);
});

it('returns 404 for a reference paid by another transaction', function () {
    Event::fake([MBWayReferencePaid::class]);
    createPendingMbwayReference(['state' => 1, 'transaction_id' => 'TXN000']);

    $response = $this->getJson(route('eupago.callback', validMbwayCallbackPayload()));

    $response->assertNotFound();
    Event::assertNotDispatched(MBWayReferencePaid::class);
});

it('fires the event after the payment is committed', function () {
    $transactionLevel = null;
    Event::listen(MBWayReferencePaid::class, function () use (&$transactionLevel) {
        $transactionLevel = DB::transactionLevel();
    });
    createPendingMbwayReference();

    $this->getJson(route('eupago.callback', validMbwayCallbackPayload()))->assertOk();

    expect($transactionLevel)->toBe(0);
});

it('fires the model events when marking a reference as paid', function () {
    $updated = false;
    MbwayReference::updated(function () use (&$updated) {
        $updated = true;
    });
    createPendingMbwayReference();

    $this->getJson(route('eupago.callback', validMbwayCallbackPayload()))->assertOk();

    expect($updated)->toBeTrue();
});

it('keeps the payment when a listener throws, and acknowledges the retry', function () {
    $reference = createPendingMbwayReference();
    Event::listen(MBWayReferencePaid::class, fn () => throw new RuntimeException('listener failed'));

    $this->getJson(route('eupago.callback', validMbwayCallbackPayload()))->assertServerError();

    expect((int) $reference->fresh()->state)->toBe(1)
        ->and($reference->fresh()->transaction_id)->toBe('TXN999');

    Event::forget(MBWayReferencePaid::class);
    Event::fake([MBWayReferencePaid::class]);

    $this->getJson(route('eupago.callback', validMbwayCallbackPayload()))->assertOk();
    Event::assertNotDispatched(MBWayReferencePaid::class);
});
