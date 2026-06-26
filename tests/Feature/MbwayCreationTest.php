<?php

use CodeTech\EuPago\MBWay\MBWay;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;

it('creates an MBWay reference and maps the response keys', function () {
    Http::fake(['*' => Http::response([
        'sucesso' => true,
        'estado' => 0,
        'resposta' => 'OK',
        'referencia' => '900111222',
        'valor' => 15.0,
        'alias' => '912345678',
    ])]);

    $result = (new MBWay(15.0, 1, '912345678'))->create();

    expect($result['success'])->toBeTrue()
        ->and($result['reference'])->toBe('900111222')
        ->and($result['alias'])->toBe('912345678');
});

it('sends the MBWay creation request with the configured credentials', function () {
    Http::fake(['*' => Http::response(['sucesso' => true])]);

    (new MBWay(15.0, 1, '912345678'))->create();

    Http::assertSent(fn ($request) => str_contains($request->url(), 'mbway/create')
        && $request['chave'] === config('eupago.api_key')
        && $request['alias'] === '912345678');
});

it('throws when the MBWay API returns a server error', function () {
    Http::fake(['*' => Http::response('Server Error', 500)]);

    expect(fn () => (new MBWay(15.0, 1, '912345678'))->create())->toThrow(RequestException::class);
});

it('records an error when the MBWay API reports failure', function () {
    Http::fake(['*' => Http::response([
        'sucesso' => false,
        'estado' => 12,
        'resposta' => 'Erro',
    ])]);

    $mbway = new MBWay(15.0, 1, '912345678');
    $mbway->create();

    expect($mbway->hasErrors())->toBeTrue()
        ->and($mbway->getErrors())->toHaveKey(12);
});
