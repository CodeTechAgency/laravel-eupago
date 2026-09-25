---
title: PayShop
weight: 7
group: Payment methods
---

References are stored against your own models, so add the `HasPayShopReferences` trait to each
model that takes PayShop references:

```php
use CodeTech\EuPago\Traits\HasPayShopReferences;

class Order extends Model
{
    use HasPayShopReferences;
}
```

Then create the payment and save it through the trait's relationship:

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

## Creating and saving in one call

The trait can also create and persist a reference in a single call. It returns the
persisted reference on success, or the errors on failure:

```php
$reference = $order->createPayShopReference($value, $id);
```

Retrieve the PayShop references:

```php
$payShopReferences = $order->payShopReferences;
```

When the reference is paid, the [callback](callbacks.md) fires a `PayShopReferencePaid`
event and stores the Eupago transaction on the reference, so a paid reference can be
[refunded](refunds.md) through `$reference->transaction_id`.
