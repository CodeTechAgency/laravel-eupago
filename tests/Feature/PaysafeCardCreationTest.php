<?php

use CodeTech\EuPago\PaysafeCard\PaysafeCard;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;

it('creates a PaysafeCard reference and maps the redirect URL', function () {
    Http::fake(['*' => Http::response([
        'sucesso' => true,
        'estado' => 0,
        'resposta' => 'OK',
        'referencia' => '000017428',
        'url' => 'https://sandbox.eupago.pt/paysafecard/pay/abc123',
        'valor' => '25.00000',
    ])]);

    $result = (new PaysafeCard(25.00, 'order-49', 'https://shop.test/return'))->create();

    expect($result['success'])->toBeTrue()
        ->and($result['response'])->toBe('OK')
        ->and($result['identifier'])->toBe('order-49')
        ->and($result['reference'])->toBe('000017428')
        ->and($result['url'])->toBe('https://sandbox.eupago.pt/paysafecard/pay/abc123')
        ->and($result['value'])->toBe(25.00);
});

it('sends the PaysafeCard creation request with the configured credentials', function () {
    Http::fake(['*' => Http::response(['sucesso' => true])]);

    (new PaysafeCard(25.00, 'order-49', 'https://shop.test/return'))->create();

    Http::assertSent(fn ($request) => str_contains($request->url(), 'paysafecard/create')
        && $request['chave'] === config('eupago.api_key')
        && $request['valor'] == 25.00
        && $request['id'] === 'order-49'
        && $request['url_retorno'] === 'https://shop.test/return');
});

it('omits url_retorno when no return URL is given', function () {
    Http::fake(['*' => Http::response(['sucesso' => true])]);

    (new PaysafeCard(25.00, 'order-49'))->create();

    Http::assertSent(fn ($request) => ! array_key_exists('url_retorno', $request->data()));
});

it('handles a malformed 2xx response body gracefully', function () {
    Http::fake(['*' => Http::response('not valid json', 200)]);

    $paysafeCard = new PaysafeCard(25.00, 'order-49');
    $result = $paysafeCard->create();

    expect($paysafeCard->hasErrors())->toBeTrue()
        ->and($paysafeCard->getErrors())->toHaveKey('unknown')
        ->and($result['url'])->toBeNull();
});

it('throws when the PaysafeCard API returns a server error', function () {
    Http::fake(['*' => Http::response('Server Error', 500)]);

    expect(fn () => (new PaysafeCard(25.00, 'order-49'))->create())->toThrow(RequestException::class);
});

it('records an error when the PaysafeCard API reports failure', function () {
    Http::fake(['*' => Http::response([
        'sucesso' => false,
        'estado' => 11,
        'resposta' => 'Chave de API inv&aacute;lida',
    ])]);

    $paysafeCard = new PaysafeCard(25.00, 'order-49');
    $paysafeCard->create();

    expect($paysafeCard->hasErrors())->toBeTrue()
        ->and($paysafeCard->getErrors())->toHaveKey(11);
});
