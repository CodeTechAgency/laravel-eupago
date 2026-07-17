---
title: Querying reference status
weight: 10
group: Handling payments
---

Besides the [callback](callbacks.md), you can query a reference's current status on
demand — useful for reconciliation or when a callback is missed or delayed. Eupago
resolves any reference type (MB, MB WAY, PayShop) through a single reference-info
endpoint, so the same call works regardless of how the reference was created:

```php
use CodeTech\EuPago\EuPago;

$eupago = new EuPago;

try {
    $status = $eupago->status($reference, $entity);

    if ($eupago->hasErrors()) {
        // handle errors
    }
} catch (\Exception $e) {
    // handle exception
}
```

The `$entity` argument is optional. `$status` is mapped to normalized keys, where
`reference_state` holds the payment status (e.g. `"pendente"`, `"pago"`):

```php
[
    'success' => true,
    'state' => 0,
    'response' => "OK",
    'entity' => "81921",
    'reference' => "800152011",
    'identifier' => "order-123",
    'reference_state' => "pendente",
    'created_date' => "2026-06-27",
    'created_time' => "00:22:49",
    'archived' => false,
]
```
