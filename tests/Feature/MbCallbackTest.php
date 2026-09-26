<?php

use CodeTech\EuPago\Events\MBReferencePaid;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;

it('marks a pending MB reference as paid and dispatches the event', function () {
    Event::fake([MBReferencePaid::class]);
    $reference = createPendingMbReference();

    $response = $this->getJson(route('eupago.mb.callback', validMbCallbackPayload()));

    $response->assertOk()->assertJson(['response' => 'Success']);
    expect((int) $reference->fresh()->state)->toBe(1);
    // The callback's `transacao` is the id refunds are keyed by.
    expect($reference->fresh()->transaction_id)->toBe('TXN123');
    Event::assertDispatched(
        MBReferencePaid::class,
        fn (MBReferencePaid $event) => $event->reference->is($reference)
    );
});

it('returns 404 when the reference exists but the value does not match', function () {
    Event::fake([MBReferencePaid::class]);
    createPendingMbReference();

    $response = $this->getJson(route('eupago.mb.callback', validMbCallbackPayload([
        'valor' => '99.99',
    ])));

    $response->assertNotFound()->assertJson(['response' => 'No pending reference found']);
    Event::assertNotDispatched(MBReferencePaid::class);
});

it('acknowledges a redelivered payment without recording or firing it again', function () {
    Event::fake([MBReferencePaid::class]);
    $reference = createPendingMbReference();

    $this->getJson(route('eupago.mb.callback', validMbCallbackPayload()))->assertOk();
    $response = $this->getJson(route('eupago.mb.callback', validMbCallbackPayload()));

    $response->assertOk();
    expect($reference->payments()->count())->toBe(1);
    Event::assertDispatchedTimes(MBReferencePaid::class, 1);
});

it('returns 404 for a reference paid without its payments recorded', function () {
    Event::fake([MBReferencePaid::class]);
    // Paid before payments were recorded, so a new transaction cannot be told
    // apart from a redelivery.
    $reference = createPendingMbReference(['state' => 1, 'transaction_id' => 'TXN000']);

    $response = $this->getJson(route('eupago.mb.callback', validMbCallbackPayload()));

    $response->assertNotFound();
    expect($reference->payments()->count())->toBe(0);
    Event::assertNotDispatched(MBReferencePaid::class);
});

it('records the payment and passes it to the event', function () {
    Event::fake([MBReferencePaid::class]);
    $reference = createPendingMbReference();

    $this->getJson(route('eupago.mb.callback', validMbCallbackPayload()))->assertOk();

    $payment = $reference->payments()->sole();
    expect($payment->transaction_id)->toBe('TXN123')
        ->and($payment->value)->toBe(10.50);
    Event::assertDispatched(
        MBReferencePaid::class,
        fn (MBReferencePaid $event) => $event->payment->is($payment)
    );
});

it('confirms a payment within the reference amount range', function () {
    Event::fake([MBReferencePaid::class]);
    $reference = createPendingMbReference(['min_value' => 5, 'max_value' => 20]);

    $response = $this->getJson(route('eupago.mb.callback', validMbCallbackPayload([
        'valor' => '7.25000',
    ])));

    $response->assertOk();
    expect((int) $reference->fresh()->state)->toBe(1)
        ->and($reference->payments()->sole()->value)->toBe(7.25);
});

it('returns 404 when the value is outside the reference amount range', function () {
    Event::fake([MBReferencePaid::class]);
    createPendingMbReference(['min_value' => 5, 'max_value' => 20]);

    $response = $this->getJson(route('eupago.mb.callback', validMbCallbackPayload([
        'valor' => '20.01',
    ])));

    $response->assertNotFound();
    Event::assertNotDispatched(MBReferencePaid::class);
});

it('confirms every payment of a reference that allows repeat payments', function () {
    Event::fake([MBReferencePaid::class]);
    $reference = createPendingMbReference();

    $this->getJson(route('eupago.mb.callback', validMbCallbackPayload()))->assertOk();
    $response = $this->getJson(route('eupago.mb.callback', validMbCallbackPayload([
        'transacao' => 'TXN124',
    ])));

    $response->assertOk();
    expect($reference->payments()->pluck('transaction_id')->all())->toBe(['TXN123', 'TXN124'])
        ->and($reference->fresh()->transaction_id)->toBe('TXN124');
    Event::assertDispatchedTimes(MBReferencePaid::class, 2);
});

it('fires the event after the payment is committed', function () {
    $transactionLevel = null;
    Event::listen(MBReferencePaid::class, function () use (&$transactionLevel) {
        $transactionLevel = DB::transactionLevel();
    });
    createPendingMbReference();

    $this->getJson(route('eupago.mb.callback', validMbCallbackPayload()))->assertOk();

    expect($transactionLevel)->toBe(0);
});

it('rejects an MB callback for a reference that does not exist', function () {
    $response = $this->getJson(route('eupago.mb.callback', validMbCallbackPayload([
        'referencia' => '000000000',
    ])));

    $response->assertStatus(422)->assertJsonStructure(['referencia']);
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
