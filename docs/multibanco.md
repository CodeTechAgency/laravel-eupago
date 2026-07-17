---
title: Multibanco (MB)
weight: 5
group: Payment methods
---

Create an MB reference:

```php
use CodeTech\EuPago\MB\MB;

$order = Order::find(1);

$mb = new MB(
    $order->value,        // payment value
    $order->id,           // your identifier, echoed back in the callback
    now(),                // start date
    now()->addDays(3),    // end date (payment limit)
    $order->value,        // minimum accepted value
    $order->value,        // maximum accepted value
    false                 // allow duplicated payments
);

try {
    $mbReferenceData = $mb->create();

    if ($mb->hasErrors()) {
        // handle errors
    }

    $order->mbReferences()->create($mbReferenceData);
} catch (\Exception $e) {
    // handle exception
}
```

`$mbReferenceData` contains the normalized payment information:

```php
[
    'success' => true,
    'state' => 0,
    'response' => "OK",
    'entity' => "82167",
    'reference' => "000001236",
    'value' => "3.00000",
]
```

## Using the trait

Alternatively, use the `HasMultibancoReferences` trait on the models for which you want
to generate MB references:

```php
use CodeTech\EuPago\Traits\HasMultibancoReferences;

class Order extends Model
{
    use HasMultibancoReferences;
}
```

With the trait applied, you can create and persist a reference in a single call. It
returns the persisted reference on success, or the errors on failure:

```php
$reference = $order->createMbReference($value, $id, $startDate, $endDate, $minValue, $maxValue);
```

Retrieve the MB references:

```php
$mbReferences = $order->mbReferences;
```

When the reference is paid, the [callback](callbacks.md) fires an `MBReferencePaid` event.
