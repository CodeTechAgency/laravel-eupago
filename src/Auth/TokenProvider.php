<?php

namespace CodeTech\EuPago\Auth;

use CodeTech\EuPago\EuPago;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class TokenProvider
{
    /**
     * The token endpoint.
     */
    const URI = '/api/auth/token';

    /**
     * Seconds subtracted from the token lifetime when caching, so a cached
     * token is never handed out right at the edge of its expiry.
     */
    const EXPIRY_MARGIN = 60;

    /**
     * Returns a bearer token for the management API, requesting a new one
     * when no valid token is cached.
     *
     * @throws ConnectionException
     * @throws RequestException
     */
    public function token(): string
    {
        return Cache::get($this->cacheKey()) ?? $this->requestToken();
    }

    /**
     * Requests a token through the client credentials grant and caches it
     * for the lifetime reported by Eupago.
     *
     * @throws ConnectionException
     * @throws RequestException
     */
    protected function requestToken(): string
    {
        $response = Http::asForm()->post((new EuPago)->getBaseUri().self::URI, [
            'client_id' => config('eupago.client_id'),
            'client_secret' => config('eupago.client_secret'),
            'grant_type' => 'client_credentials',
        ])->throw();

        $tokenData = $response->json();

        $token = is_array($tokenData) ? ($tokenData['access_token'] ?? null) : null;

        if (! is_string($token) || $token === '') {
            throw new \RuntimeException('The Eupago token response did not contain an access token.');
        }

        $expiresIn = (int) ($tokenData['expires_in'] ?? 0);

        if ($expiresIn > self::EXPIRY_MARGIN) {
            Cache::put($this->cacheKey(), $token, $expiresIn - self::EXPIRY_MARGIN);
        }

        return $token;
    }

    /**
     * Returns the cache key holding the token, scoped so switching the
     * environment or the client never reuses a stale token.
     */
    protected function cacheKey(): string
    {
        return 'eupago.token.'.md5(config('eupago.env').'|'.config('eupago.client_id'));
    }
}
