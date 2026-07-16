---
title: Refunds
weight: 11
---

Paid transactions can be refunded, partially or in full, through Eupago's management
API. Refunds require the [OAuth client credentials](configuration.md#oauth-client-credentials)
to be configured. The refund is keyed by the transaction id — the `transacao` value
delivered by the payment [callback](callbacks.md):

```php
use CodeTech\EuPago\EuPago;

$eupago = new EuPago;

try {
    $result = $eupago->refund($transactionId, 10.50);

    if ($eupago->hasErrors()) {
        // handle rejection (e.g. refund larger than the payment)
    }
} catch (\Exception $e) {
    // handle exception
}
```

`refund()` also accepts an optional reason and, for payment methods without a direct
refund path, the destination bank account:

```php
$eupago->refund($transactionId, 10.50, reason: 'duplicate order', iban: 'PT50...', bic: 'BPOTPTPL');
```

The result is mapped to normalized keys:

```php
[
    'success' => true,
    'status' => "Success",
    'refund_id' => "12345",
    'code' => null,
    'text' => null,
]
```

When Eupago rejects the refund (`status` `"Rejected"`), the rejection's `code` and `text`
are also added to the error bag. Transport and server errors throw, mirroring `create()`
and `status()`.
