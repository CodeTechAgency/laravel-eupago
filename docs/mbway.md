---
title: MB WAY
weight: 6
group: Payment methods
---

Create an MB WAY payment request — the customer confirms it on their phone through the
MB WAY app.

References are stored against your own models, so add the `HasMbWayReferences` trait to each
model that takes MB WAY payments:

```php
use CodeTech\EuPago\Traits\HasMbWayReferences;

class Order extends Model
{
    use HasMbWayReferences;
}
```

Then create the payment and save it through the trait's relationship:

```php
use CodeTech\EuPago\MBWay\MBWay;

$order = Order::find(1);

$mbway = new MBWay(
    $order->value,     // payment value
    $order->id,        // your identifier, echoed back as `identificador` in the callback
    '912345678',       // the customer's MB WAY alias (phone number)
    'Order #1'         // optional description
);

try {
    $mbwayReferenceData = $mbway->create();

    if ($mbway->hasErrors()) {
        // handle errors
    }

    $order->mbwayReferences()->create($mbwayReferenceData);
} catch (\Exception $e) {
    // handle exception
}
```

## Creating and saving in one call

The trait can also create and persist a reference in a single call. It returns the
persisted reference on success, or the errors on failure:

```php
$reference = $order->createMbwayReference($value, $id, $alias);
```

Retrieve the MB WAY references:

```php
$mbwayReferences = $order->mbwayReferences;
```

When the payment is confirmed, the [callback](callbacks.md) fires an `MBWayReferencePaid`
event and stores the Eupago transaction on the reference, so a paid reference can be
[refunded](refunds.md) through `$reference->transaction_id`.
