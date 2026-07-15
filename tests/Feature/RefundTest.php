<?php

use CodeTech\EuPago\EuPago;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;

function fakeRefund(array $refundResponse, int $refundStatus = 201): void
{
    Http::fake([
        '*/api/auth/token' => Http::response([
            'access_token' => 'the-token',
            'token_type' => 'Bearer',
            'expires_in' => 3600,
        ]),
        '*/api/management/*' => Http::response($refundResponse, $refundStatus),
    ]);
}

it('refunds a transaction and maps the response keys', function () {
    fakeRefund(['transactionStatus' => 'Success', 'refundId' => '12345']);

    $eupago = new EuPago;
    $result = $eupago->refund(987654, 10.50);

    expect($result['success'])->toBeTrue()
        ->and($result['status'])->toBe('Success')
        ->and($result['refund_id'])->toBe('12345')
        ->and($eupago->hasErrors())->toBeFalse();
});

it('sends a bearer-authenticated request keyed by the transaction id', function () {
    fakeRefund(['transactionStatus' => 'Success', 'refundId' => '12345']);

    (new EuPago)->refund(987654, 10.50);

    Http::assertSent(fn ($request) => str_ends_with($request->url(), '/api/management/v1.02/refund/987654')
        && $request->hasHeader('Authorization', 'Bearer the-token')
        && $request['amount'] === 10.5);
});

it('sends the optional refund details when provided', function () {
    fakeRefund(['transactionStatus' => 'Success', 'refundId' => '12345']);

    (new EuPago)->refund(987654, 10.50, 'duplicate order', 'PT50000201231234567890154', 'BPOTPTPL');

    Http::assertSent(fn ($request) => str_contains($request->url(), '/refund/')
        && $request['reason'] === 'duplicate order'
        && $request['iban'] === 'PT50000201231234567890154'
        && $request['bic'] === 'BPOTPTPL');
});

it('omits the optional refund details when not provided', function () {
    fakeRefund(['transactionStatus' => 'Success', 'refundId' => '12345']);

    (new EuPago)->refund(987654, 10.50);

    Http::assertSent(fn ($request) => str_contains($request->url(), '/refund/')
        && ! isset($request['reason'])
        && ! isset($request['iban'])
        && ! isset($request['bic']));
});

it('records an error when the refund is rejected', function () {
    fakeRefund([
        'transactionStatus' => 'Rejected',
        'code' => 'DIRECT_REFUND_NOT_ALLOWED',
        'text' => 'Direct Refund Not Allowed',
    ], 400);

    $eupago = new EuPago;
    $result = $eupago->refund(987654, 10.50);

    expect($result['success'])->toBeFalse()
        ->and($result['status'])->toBe('Rejected')
        ->and($result['refund_id'])->toBeNull()
        ->and($eupago->hasErrors())->toBeTrue()
        ->and($eupago->getErrors())->toHaveKey('DIRECT_REFUND_NOT_ALLOWED');
});

it('throws when the refund API returns a server error', function () {
    fakeRefund([], 500);

    expect(fn () => (new EuPago)->refund(987654, 10.50))->toThrow(RequestException::class);
});

it('reuses the cached token across refunds', function () {
    fakeRefund(['transactionStatus' => 'Success', 'refundId' => '12345']);

    (new EuPago)->refund(987654, 10.50);
    (new EuPago)->refund(987655, 5.00);

    Http::assertSentCount(3);
});
