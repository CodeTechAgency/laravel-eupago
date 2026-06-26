<?php

use CodeTech\EuPago\PayShop\PayShop;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;

it('creates a PayShop reference and maps the response keys', function () {
    Http::fake(['*' => Http::response([
        'sucesso' => true,
        'estado' => 0,
        'resposta' => 'OK',
        'referencia' => 555444333,
        'valor' => '20.00',
    ])]);

    $result = (new PayShop(20.00, '555'))->create();

    expect($result['success'])->toBeTrue()
        ->and($result['reference'])->toBe(555444333)
        ->and($result['value'])->toBe('20.00');
});

it('sends the PayShop creation request with the configured credentials', function () {
    Http::fake(['*' => Http::response(['sucesso' => true])]);

    (new PayShop(20.00, '555'))->create();

    Http::assertSent(fn ($request) => str_contains($request->url(), 'payshop/create')
        && $request['chave'] === config('eupago.api_key')
        && $request['valor'] == 20.00);
});

it('handles a malformed 2xx response body gracefully', function () {
    Http::fake(['*' => Http::response('not valid json', 200)]);

    $payShop = new PayShop(20.00, '555');
    $result = $payShop->create();

    expect($payShop->hasErrors())->toBeTrue()
        ->and($result['reference'])->toBeNull();
});

it('throws when the PayShop API returns a server error', function () {
    Http::fake(['*' => Http::response('Server Error', 500)]);

    expect(fn () => (new PayShop(20.00, '555'))->create())->toThrow(RequestException::class);
});

it('records an error when the PayShop API reports failure', function () {
    Http::fake(['*' => Http::response([
        'sucesso' => false,
        'estado' => 13,
        'resposta' => 'Erro',
    ])]);

    $payShop = new PayShop(20.00, '555');
    $payShop->create();

    expect($payShop->hasErrors())->toBeTrue()
        ->and($payShop->getErrors())->toHaveKey(13);
});
