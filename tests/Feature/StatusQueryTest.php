<?php

use CodeTech\EuPago\EuPago;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;

it('queries a reference status and maps the response keys', function () {
    Http::fake(['*' => Http::response([
        'entidade' => '81921',
        'referencia' => '800152011',
        'identificador' => 'order-123',
        'estado' => 0,
        'data_criacao' => '2026-06-27',
        'hora_criacao' => '00:22:49',
        'estado_referencia' => 'pendente',
        'arquivada' => false,
        'sucesso' => true,
        'resposta' => 'OK',
    ])]);

    $result = (new EuPago)->status('800152011', '81921');

    expect($result['success'])->toBeTrue()
        ->and($result['reference'])->toBe('800152011')
        ->and($result['entity'])->toBe('81921')
        ->and($result['identifier'])->toBe('order-123')
        ->and($result['reference_state'])->toBe('pendente')
        ->and($result['created_date'])->toBe('2026-06-27')
        ->and($result['archived'])->toBeFalse();
});

it('sends the status request with the configured credentials and reference', function () {
    Http::fake(['*' => Http::response(['sucesso' => true])]);

    (new EuPago)->status('999888777', '12345');

    Http::assertSent(fn ($request) => str_contains($request->url(), 'multibanco/info')
        && $request['chave'] === config('eupago.api_key')
        && $request['referencia'] === '999888777'
        && $request['entidade'] === '12345');
});

it('omits the entity from the request when none is provided', function () {
    Http::fake(['*' => Http::response(['sucesso' => true])]);

    (new EuPago)->status('999888777');

    Http::assertSent(fn ($request) => ! isset($request['entidade']));
});

it('throws when the status API returns a server error', function () {
    Http::fake(['*' => Http::response('Server Error', 500)]);

    expect(fn () => (new EuPago)->status('999888777'))->toThrow(RequestException::class);
});

it('records an error when the status API reports failure', function () {
    Http::fake(['*' => Http::response([
        'sucesso' => false,
        'estado' => 11,
        'resposta' => 'Chave de API inv&aacute;lida',
    ])]);

    $eupago = new EuPago;
    $eupago->status('999888777');

    expect($eupago->hasErrors())->toBeTrue()
        ->and($eupago->getErrors())->toHaveKey(11);
});
