<?php

use CodeTech\EuPago\Auth\TokenProvider;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;

it('requests a token through the client credentials grant', function () {
    Http::fake(['*' => Http::response([
        'access_token' => 'the-token',
        'token_type' => 'Bearer',
        'expires_in' => 3600,
    ])]);

    $token = (new TokenProvider)->token();

    expect($token)->toBe('the-token');

    Http::assertSent(fn ($request) => str_contains($request->url(), '/api/auth/token')
        && $request['client_id'] === 'test-client-id'
        && $request['client_secret'] === 'test-client-secret'
        && $request['grant_type'] === 'client_credentials');
});

it('caches the token for the lifetime reported by Eupago', function () {
    Http::fake(['*' => Http::response([
        'access_token' => 'the-token',
        'expires_in' => 3600,
    ])]);

    (new TokenProvider)->token();
    $token = (new TokenProvider)->token();

    expect($token)->toBe('the-token');
    Http::assertSentCount(1);
});

it('does not cache a token that expires within the safety margin', function () {
    Http::fake(['*' => Http::response([
        'access_token' => 'short-lived',
        'expires_in' => TokenProvider::EXPIRY_MARGIN,
    ])]);

    (new TokenProvider)->token();
    (new TokenProvider)->token();

    Http::assertSentCount(2);
});

it('throws when the token endpoint rejects the credentials', function () {
    Http::fake(['*' => Http::response(['error' => 'invalid_client'], 400)]);

    expect(fn () => (new TokenProvider)->token())->toThrow(RequestException::class);
});

it('throws when the token response contains no access token', function () {
    Http::fake(['*' => Http::response(['token_type' => 'Bearer'])]);

    expect(fn () => (new TokenProvider)->token())->toThrow(RuntimeException::class);
});
