---
title: PayShop
weight: 7
group: Payment methods
---

Create a PayShop reference:

```php
use CodeTech\EuPago\PayShop\PayShop;

$order = Order::find(1);

$payShop = new PayShop(
    $order->value,   // payment value
    $order->id       // your identifier, echoed back in the callback
);

try {
    $payShopReferenceData = $payShop->create();

    if ($payShop->hasErrors()) {
        // handle errors
    }

    $order->payShopReferences()->create($payShopReferenceData);
} catch (\Exception $e) {
    // handle exception
}
```

`$payShopReferenceData` contains the normalized payment information:

```php
[
    'success' => true,
    'state' => 0,
    'response' => "OK",
    'reference' => 1800000132722,
    'value' => "10.00000",
]
```

## Using the trait

Alternatively, use the `HasPayShopReferences` trait:

```php
use CodeTech\EuPago\Traits\HasPayShopReferences;

class Order extends Model
{
    use HasPayShopReferences;
}
```

```php
$reference = $order->createPayShopReference($value, $id);
```

Retrieve the PayShop references:

```php
$payShopReferences = $order->payShopReferences;
```

When the reference is paid, the [callback](callbacks.md) fires a `PayShopReferencePaid` event.
