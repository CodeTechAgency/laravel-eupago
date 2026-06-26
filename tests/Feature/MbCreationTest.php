<?php

use Carbon\Carbon;
use CodeTech\EuPago\MB\MB;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;

function makeMb(): MB
{
    return new MB(10.50, '1', Carbon::parse('2026-06-01'), Carbon::parse('2026-06-30'), 0, 100);
}

it('creates an MB reference and maps the response keys', function () {
    Http::fake(['*' => Http::response([
        'sucesso' => true,
        'estado' => 0,
        'resposta' => 'OK',
        'entidade' => '12345',
        'referencia' => '999888777',
        'valor' => 10.50,
        'valor_minimo' => 0,
        'valor_maximo' => 100,
        'data_inicio' => '2026-06-01',
        'data_fim' => '2026-06-30',
    ])]);

    $result = makeMb()->create();

    expect($result['success'])->toBeTrue()
        ->and($result['reference'])->toBe('999888777')
        ->and($result['entity'])->toBe('12345');
});

it('sends the MB creation request with the configured credentials', function () {
    Http::fake(['*' => Http::response(['sucesso' => true])]);

    makeMb()->create();

    Http::assertSent(fn ($request) => str_contains($request->url(), 'multibanco/create')
        && $request['chave'] === config('eupago.api_key')
        && $request['valor'] == 10.50);
});

it('throws when the MB API returns a server error', function () {
    Http::fake(['*' => Http::response('Server Error', 500)]);

    expect(fn () => makeMb()->create())->toThrow(RequestException::class);
});

it('records an error when the MB API reports failure', function () {
    Http::fake(['*' => Http::response([
        'sucesso' => false,
        'estado' => 11,
        'resposta' => 'Chave de API inv&aacute;lida',
    ])]);

    $mb = makeMb();
    $mb->create();

    expect($mb->hasErrors())->toBeTrue()
        ->and($mb->getErrors())->toHaveKey(11);
});
