<?php

use CodeTech\EuPago\Paysafecard\Paysafecard;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;

it('creates a Paysafecard reference and maps the redirect URL', function () {
    Http::fake(['*' => Http::response([
        'sucesso' => true,
        'estado' => 0,
        'resposta' => 'https://sandbox.eupago.pt/paysafecard/pay/abc123',
    ])]);

    $result = (new Paysafecard(25.00, 'order-49', 'https://shop.test/return'))->create();

    expect($result['success'])->toBeTrue()
        ->and($result['identifier'])->toBe('order-49')
        ->and($result['url'])->toBe('https://sandbox.eupago.pt/paysafecard/pay/abc123')
        ->and($result['value'])->toBe(25.00);
});

it('sends the Paysafecard creation request with the configured credentials', function () {
    Http::fake(['*' => Http::response(['sucesso' => true])]);

    (new Paysafecard(25.00, 'order-49', 'https://shop.test/return'))->create();

    Http::assertSent(fn ($request) => str_contains($request->url(), 'paysafecard/create')
        && $request['chave'] === config('eupago.api_key')
        && $request['valor'] == 25.00
        && $request['id'] === 'order-49'
        && $request['url_retorno'] === 'https://shop.test/return');
});

it('omits url_retorno when no return URL is given', function () {
    Http::fake(['*' => Http::response(['sucesso' => true])]);

    (new Paysafecard(25.00, 'order-49'))->create();

    Http::assertSent(fn ($request) => ! array_key_exists('url_retorno', $request->data()));
});

it('handles a malformed 2xx response body gracefully', function () {
    Http::fake(['*' => Http::response('not valid json', 200)]);

    $paysafecard = new Paysafecard(25.00, 'order-49');
    $result = $paysafecard->create();

    expect($paysafecard->hasErrors())->toBeTrue()
        ->and($paysafecard->getErrors())->toHaveKey('unknown')
        ->and($result['url'])->toBeNull();
});

it('throws when the Paysafecard API returns a server error', function () {
    Http::fake(['*' => Http::response('Server Error', 500)]);

    expect(fn () => (new Paysafecard(25.00, 'order-49'))->create())->toThrow(RequestException::class);
});

it('records an error when the Paysafecard API reports failure', function () {
    Http::fake(['*' => Http::response([
        'sucesso' => false,
        'estado' => 11,
        'resposta' => 'Chave de API inv&aacute;lida',
    ])]);

    $paysafecard = new Paysafecard(25.00, 'order-49');
    $paysafecard->create();

    expect($paysafecard->hasErrors())->toBeTrue()
        ->and($paysafecard->getErrors())->toHaveKey(11);
});
