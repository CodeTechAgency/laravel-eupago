<?php

use CodeTech\EuPago\Models\MbReference;
use CodeTech\EuPago\Models\MbwayReference;
use CodeTech\EuPago\Models\PayShopReference;
use CodeTech\EuPago\Tests\TestCase;

uses(TestCase::class)->in(__DIR__);

/*
|--------------------------------------------------------------------------
| Test helpers
|--------------------------------------------------------------------------
*/

/**
 * Persist a pending MB reference. The morph columns are not fillable, so
 * they are set explicitly (the callback flow never touches the parent).
 */
function createPendingMbReference(array $overrides = []): MbReference
{
    $reference = new MbReference(array_merge([
        'entity' => '12345',
        'reference' => '123456789',
        'value' => 10.50,
        'start_date' => '2026-06-01',
        'end_date' => '2026-06-30',
        'min_value' => 0,
        'max_value' => 100,
        'state' => 0,
    ], $overrides));

    $reference->mbable_id = 1;
    $reference->mbable_type = 'Tests\\Dummy';
    $reference->save();

    return $reference;
}

function createPendingMbwayReference(array $overrides = []): MbwayReference
{
    $reference = new MbwayReference(array_merge([
        'reference' => 987654321,
        'value' => 15.00,
        'alias' => '912345678',
        'state' => 0,
    ], $overrides));

    $reference->mbwayable_id = 1;
    $reference->mbwayable_type = 'Tests\\Dummy';
    $reference->save();

    return $reference;
}

/**
 * A valid EuPago callback payload, matching the default seeded reference.
 */
function validMbCallbackPayload(array $overrides = []): array
{
    return array_merge([
        'valor' => '10.50',
        'canal' => config('eupago.channel'),
        'referencia' => '123456789',
        'transacao' => 'TXN123',
        'identificador' => 'ID123',
        'mp' => 'MB',
        'chave_api' => config('eupago.api_key'),
        'data' => now()->format('Y-m-d:H:i:s'),
        'entidade' => '12345',
        'comissao' => '0.50',
    ], $overrides);
}

function validMbwayCallbackPayload(array $overrides = []): array
{
    return array_merge([
        'valor' => '15.00',
        'canal' => config('eupago.channel'),
        'referencia' => '987654321',
        'transacao' => 'TXN999',
        'identificador' => 'ID999',
        'mp' => 'MBWAY',
        'chave_api' => config('eupago.api_key'),
        'data' => now()->format('Y-m-d:H:i:s'),
        'entidade' => '54321',
        'comissao' => '0.30',
    ], $overrides);
}

function createPendingPayShopReference(array $overrides = []): PayShopReference
{
    $reference = new PayShopReference(array_merge([
        'reference' => 555444333,
        'value' => 20.00,
        'state' => 0,
    ], $overrides));

    $reference->payshopable_id = 1;
    $reference->payshopable_type = 'Tests\\Dummy';
    $reference->save();

    return $reference;
}

function validPayShopCallbackPayload(array $overrides = []): array
{
    return array_merge([
        'valor' => '20.00',
        'canal' => config('eupago.channel'),
        'referencia' => '555444333',
        'transacao' => 'TXN555',
        'identificador' => 'ID555',
        'mp' => 'PS',
        'chave_api' => config('eupago.api_key'),
        'data' => now()->format('Y-m-d:H:i:s'),
        'entidade' => '00000',
        'comissao' => '0.20',
    ], $overrides);
}
