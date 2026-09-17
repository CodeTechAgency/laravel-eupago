<?php

use CodeTech\EuPago\CreditCard\CreditCard;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;

function newCreditCard(?string $lang = null, bool $notify = true): CreditCard
{
    return new CreditCard(
        30.00,
        'order-50',
        'https://shop.test/success',
        'https://shop.test/fail',
        'https://shop.test/back',
        'customer@shop.test',
        $lang,
        $notify
    );
}

it('creates a Credit Card payment and maps the redirect URL', function () {
    Http::fake(['*' => Http::response([
        'transactionStatus' => 'Success',
        'transactionID' => '6526ds26653sad5489sa32',
        'reference' => '00235',
        'redirectUrl' => 'https://sandbox.eupago.pt/api/extern/creditcard/form/6526ds26653sad5489sa32',
    ])]);

    $creditCard = newCreditCard();
    $result = $creditCard->create();

    expect($result['success'])->toBeTrue()
        ->and($result['status'])->toBe('Success')
        ->and($result['identifier'])->toBe('order-50')
        ->and($result['form_transaction_id'])->toBe('6526ds26653sad5489sa32')
        ->and($result['reference'])->toBe('00235')
        ->and($result['url'])->toBe('https://sandbox.eupago.pt/api/extern/creditcard/form/6526ds26653sad5489sa32')
        ->and($result['value'])->toBe(30.00)
        ->and($creditCard->hasErrors())->toBeFalse();
});

it('sends an API-key-authenticated JSON request to the v1.02 endpoint', function () {
    Http::fake(['*' => Http::response(['transactionStatus' => 'Success'])]);

    newCreditCard('EN')->create();

    Http::assertSent(fn ($request) => str_ends_with($request->url(), '/api/v1.02/creditcard/create')
        && $request->hasHeader('Authorization', 'ApiKey '.config('eupago.api_key'))
        && $request->isJson()
        && $request['payment']['identifier'] === 'order-50'
        && $request['payment']['amount']['value'] === 30.0
        && $request['payment']['amount']['currency'] === 'EUR'
        && $request['payment']['successUrl'] === 'https://shop.test/success'
        && $request['payment']['failUrl'] === 'https://shop.test/fail'
        && $request['payment']['backUrl'] === 'https://shop.test/back'
        && $request['payment']['lang'] === 'EN'
        && $request['customer']['email'] === 'customer@shop.test'
        && $request['customer']['notify'] === true);
});

it('omits lang when no language is given', function () {
    Http::fake(['*' => Http::response(['transactionStatus' => 'Success'])]);

    newCreditCard()->create();

    Http::assertSent(fn ($request) => ! array_key_exists('lang', $request['payment']));
});

it('lets the customer notification be switched off', function () {
    Http::fake(['*' => Http::response(['transactionStatus' => 'Success'])]);

    newCreditCard(null, false)->create();

    Http::assertSent(fn ($request) => $request['customer']['notify'] === false);
});

it('records an error when the Credit Card request is rejected', function () {
    Http::fake(['*' => Http::response([
        'transactionStatus' => 'Rejected',
        'code' => 'APIKEY_MISSING',
        'text' => 'API Key was not available in the request',
    ], 401)]);

    $creditCard = newCreditCard();
    $result = $creditCard->create();

    expect($creditCard->hasErrors())->toBeTrue()
        ->and($creditCard->getErrors())->toHaveKey('APIKEY_MISSING')
        ->and($result['success'])->toBeFalse()
        ->and($result['url'])->toBeNull();
});

it('throws on a client error without a structured body', function () {
    Http::fake(['*' => Http::response('Forbidden', 403)]);

    expect(fn () => newCreditCard()->create())->toThrow(RequestException::class);
});

it('throws when the Credit Card API returns a server error', function () {
    Http::fake(['*' => Http::response('Server Error', 500)]);

    expect(fn () => newCreditCard()->create())->toThrow(RequestException::class);
});

it('handles a malformed 2xx response body gracefully', function () {
    Http::fake(['*' => Http::response('not valid json', 200)]);

    $creditCard = newCreditCard();
    $result = $creditCard->create();

    expect($creditCard->hasErrors())->toBeTrue()
        ->and($creditCard->getErrors())->toHaveKey('unknown')
        ->and($result['url'])->toBeNull();
});
