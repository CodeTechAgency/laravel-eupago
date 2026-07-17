---
title: MB WAY
weight: 6
group: Payment methods
---

Create an MB WAY payment request — the customer confirms it on their phone through the
MB WAY app:

```php
use CodeTech\EuPago\MBWay\MBWay;

$order = Order::find(1);

$mbway = new MBWay(
    $order->value,     // payment value
    $order->id,        // your identifier (int), echoed back as `identificador` in the callback
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

## Using the trait

Alternatively, use the `HasMbWayReferences` trait:

```php
use CodeTech\EuPago\Traits\HasMbWayReferences;

class Order extends Model
{
    use HasMbWayReferences;
}
```

```php
$reference = $order->createMbwayReference($value, $id, $alias);
```

Retrieve the MB WAY references:

```php
$mbwayReferences = $order->mbwayReferences;
```

When the payment is confirmed, the [callback](callbacks.md) fires an `MBWayReferencePaid` event.
