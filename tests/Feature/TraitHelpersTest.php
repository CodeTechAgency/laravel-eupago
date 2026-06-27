<?php

use CodeTech\EuPago\Models\MbReference;
use CodeTech\EuPago\Models\MbwayReference;
use CodeTech\EuPago\Models\PayShopReference;
use CodeTech\EuPago\Traits\Mbable;
use CodeTech\EuPago\Traits\Mbwayable;
use CodeTech\EuPago\Traits\PayShopable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;

class DummyPayable extends Model
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

it('returns the errors and persists nothing when the API reports failure', function () {
    Http::fake(['*' => Http::response([
        'sucesso' => false, 'estado' => 11, 'resposta' => 'Chave de API inv&aacute;lida',
    ])]);

    $result = $this->payable->createPayShopReference(20.00, '1');

    expect($result)->toBeArray()
        ->and($result)->toHaveKey(11)
        ->and($this->payable->payShopReferences()->count())->toBe(0);
});
