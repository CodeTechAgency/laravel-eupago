<?php

use CodeTech\EuPago\Models\MbReferencePayment;
use Illuminate\Support\Facades\Schema;

it('backfills the payment of references paid before the payments table', function () {
    $migration = include __DIR__.'/../../database/migrations/2026_09_26_000000_create_mb_reference_payments_table.php';
    $migration->down();

    $paid = createPendingMbReference(['reference' => '111', 'state' => 1, 'transaction_id' => 'TXN1']);
    createPendingMbReference(['reference' => '222', 'state' => 0]);
    // Paid before the transaction was stored: there is nothing to key it by.
    createPendingMbReference(['reference' => '333', 'state' => 1]);

    $migration->up();

    expect(Schema::hasTable('mb_reference_payments'))->toBeTrue();
    $payment = $paid->payments()->sole();
    expect($payment->transaction_id)->toBe('TXN1')
        ->and($payment->value)->toBe(10.50);
    expect(MbReferencePayment::count())->toBe(1);
});
