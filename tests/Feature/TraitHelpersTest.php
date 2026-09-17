<?php

use CodeTech\EuPago\Models\CreditCardReference;
use CodeTech\EuPago\Models\MbReference;
use CodeTech\EuPago\Models\MbwayReference;
use CodeTech\EuPago\Models\PaysafeCardReference;
use CodeTech\EuPago\Models\PayShopReference;
use CodeTech\EuPago\Traits\HasCreditCardReferences;
use CodeTech\EuPago\Traits\HasMbWayReferences;
use CodeTech\EuPago\Traits\HasMultibancoReferences;
use CodeTech\EuPago\Traits\HasPaysafeCardReferences;
use CodeTech\EuPago\Traits\HasPayShopReferences;
use CodeTech\EuPago\Traits\Mbable;
use CodeTech\EuPago\Traits\Mbwayable;
use CodeTech\EuPago\Traits\PayShopable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;

class DummyPayable extends Model
{
    use HasCreditCardReferences, HasMbWayReferences, HasMultibancoReferences, HasPaysafeCardReferences, HasPayShopReferences;

    protected $table = 'dummy_payables';

    public $timestamps = false;

    protected $guarded = [];
}

/**
 * Uses the deprecated trait aliases — guards backwards compatibility until v4.
 */
class DummyLegacyPayable extends Model
{
    use Mbable, Mbwayable, PayShopable;

    protected $table = 'dummy_payables';

    public $timestamps = false;

    protected $guarded = [];
}

beforeEach(function () {
    if (! Schema::hasTable('dummy_payables')) {
        Schema::create('dummy_payables', fn (Blueprint $table) => $table->id());
    }

    $this->payable = DummyPayable::create();
});

it('creates and persists an MB reference via the trait helper', function () {
    Http::fake(['*' => Http::response([
        'sucesso' => true, 'estado' => 0, 'resposta' => 'OK',
        'entidade' => '12345', 'referencia' => '123456789', 'valor' => 10.50,
        'valor_minimo' => 0, 'valor_maximo' => 100,
        'data_inicio' => '2026-06-01', 'data_fim' => '2026-06-30',
    ])]);

    $reference = $this->payable->createMbReference(10.50, '1', now(), now()->addMonth(), 0, 100);

    expect($reference)->toBeInstanceOf(MbReference::class)
        ->and($reference->reference)->toBe('123456789')
        ->and($this->payable->mbReferences()->count())->toBe(1);
});

it('creates and persists an MB Way reference via the trait helper', function () {
    Http::fake(['*' => Http::response([
        'sucesso' => true, 'estado' => 0, 'resposta' => 'OK',
        'referencia' => '987654321', 'valor' => 15.00, 'alias' => '912345678',
    ])]);

    $reference = $this->payable->createMbwayReference(15.00, 1, '912345678');

    expect($reference)->toBeInstanceOf(MbwayReference::class)
        ->and((string) $reference->reference)->toBe('987654321')
        ->and($this->payable->mbwayReferences()->count())->toBe(1);
});

it('creates and persists a PayShop reference via the trait helper', function () {
    Http::fake(['*' => Http::response([
        'sucesso' => true, 'estado' => 0, 'resposta' => 'OK',
        'referencia' => '555444333', 'valor' => 20.00,
    ])]);

    $reference = $this->payable->createPayShopReference(20.00, '1');

    expect($reference)->toBeInstanceOf(PayShopReference::class)
        ->and($this->payable->payShopReferences()->count())->toBe(1);
});

it('creates and persists a PaysafeCard reference via the trait helper', function () {
    Http::fake(['*' => Http::response([
        'sucesso' => true, 'estado' => 0, 'resposta' => 'OK',
        'referencia' => '000017428',
        'url' => 'https://sandbox.eupago.pt/paysafecard/pay/abc123',
    ])]);

    $reference = $this->payable->createPaysafeCardReference(25.00, 'order-49', 'https://shop.test/return');

    expect($reference)->toBeInstanceOf(PaysafeCardReference::class)
        ->and($reference->identifier)->toBe('order-49')
        ->and($reference->reference)->toBe('000017428')
        ->and($reference->url)->toBe('https://sandbox.eupago.pt/paysafecard/pay/abc123')
        ->and($this->payable->paysafeCardReferences()->count())->toBe(1);
});

it('creates and persists a Credit Card reference via the trait helper', function () {
    Http::fake(['*' => Http::response([
        'transactionStatus' => 'Success',
        'transactionID' => '6526ds26653sad5489sa32',
        'reference' => '00235',
        'redirectUrl' => 'https://sandbox.eupago.pt/api/extern/creditcard/form/6526ds26653sad5489sa32',
    ])]);

    $reference = $this->payable->createCreditCardReference(
        30.00, 'order-50', 'https://shop.test/success', 'https://shop.test/fail', 'https://shop.test/back', 'customer@shop.test'
    );

    expect($reference)->toBeInstanceOf(CreditCardReference::class)
        ->and($reference->identifier)->toBe('order-50')
        ->and($reference->form_transaction_id)->toBe('6526ds26653sad5489sa32')
        ->and($reference->transaction_id)->toBeNull()
        ->and($reference->reference)->toBe('00235')
        ->and($reference->url)->toBe('https://sandbox.eupago.pt/api/extern/creditcard/form/6526ds26653sad5489sa32')
        ->and($this->payable->creditCardReferences()->count())->toBe(1);
});

it('returns the errors and persists nothing when a Credit Card request is rejected', function () {
    Http::fake(['*' => Http::response([
        'transactionStatus' => 'Rejected', 'code' => 'APIKEY_MISSING', 'text' => 'API Key was not available in the request',
    ], 401)]);

    $result = $this->payable->createCreditCardReference(
        30.00, 'order-50', 'https://shop.test/success', 'https://shop.test/fail', 'https://shop.test/back', 'customer@shop.test'
    );

    expect($result)->toBeArray()
        ->and($result)->toHaveKey('APIKEY_MISSING')
        ->and($this->payable->creditCardReferences()->count())->toBe(0);
});

it('returns the errors and persists nothing when the API reports failure', function () {
    Http::fake(['*' => Http::response([
        'sucesso' => false, 'estado' => 11, 'resposta' => 'Chave de API inv&aacute;lida',
    ])]);

    $result = $this->payable->createPayShopReference(20.00, '1');

    expect($result)->toBeArray()
        ->and($result)->toHaveKey(11)
        ->and($this->payable->payShopReferences()->count())->toBe(0);
});

it('keeps the deprecated trait aliases working until v4', function () {
    $legacy = DummyLegacyPayable::create();

    Http::fake(['*' => Http::response([
        'sucesso' => true, 'estado' => 0, 'resposta' => 'OK',
        'referencia' => '555444333', 'valor' => 20.00,
    ])]);

    $reference = $legacy->createPayShopReference(20.00, '1');

    expect($reference)->toBeInstanceOf(PayShopReference::class)
        ->and($legacy->payShopReferences()->count())->toBe(1)
        ->and($legacy->mbReferences()->count())->toBe(0)
        ->and($legacy->mbwayReferences()->count())->toBe(0);
});
