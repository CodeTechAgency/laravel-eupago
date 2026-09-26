---
title: Multibanco (MB)
weight: 5
group: Payment methods
---

References are stored against your own models, so add the `HasMultibancoReferences` trait to each
model that takes MB references:

```php
use CodeTech\EuPago\Traits\HasMultibancoReferences;

class Order extends Model
{
    use HasMultibancoReferences;
}
```

Then create the payment and save it through the trait's relationship:

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
    'min_value' => "3.00000",
    'max_value' => "3.00000",
    'start_date' => "2026-06-01",
    'end_date' => "2026-06-04",
]
```

## Creating and saving in one call

The trait can also create and persist a reference in a single call. It
returns the persisted reference on success, or the errors on failure:

```php
$reference = $order->createMbReference($value, $id, $startDate, $endDate, $minValue, $maxValue);
```

Retrieve the MB references:

```php
$mbReferences = $order->mbReferences;
```

When the reference is paid, the [callback](callbacks.md) fires an `MBReferencePaid` event
and stores the Eupago transaction on the reference, so a paid reference can be
[refunded](refunds.md) through `$reference->transaction_id`.

A reference with a minimum and maximum value accepts any amount in that range, and one that
allows duplicated payments can be paid more than once. Each payment is recorded with the amount
paid and its transaction, and the reference keeps the latest one:

```php
$payments = $reference->payments;
```
