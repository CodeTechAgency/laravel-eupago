---
title: PaysafeCard
weight: 8
---

Unlike the reference-based methods, PaysafeCard is a **redirect flow**: Eupago
returns a payment `url` that you must redirect the customer to, along with a
`reference` for the payment (there is no entity). You also pass your own `id`
(e.g. the order id), which Eupago echoes back in the callback as `identificador`,
and you may optionally pass a `url_retorno` to control where the customer lands
after paying.

```php
use CodeTech\EuPago\PaysafeCard\PaysafeCard;

$order = Order::find(1);

$paysafeCard = new PaysafeCard(
    $order->value,
    $order->id,
    route('checkout.return') // optional url_retorno
);

try {
    $paysafeCardReferenceData = $paysafeCard->create();

    if ($paysafeCard->hasErrors()) {
        // handle errors
    }

    $reference = $order->paysafeCardReferences()->create($paysafeCardReferenceData);

    // Redirect the customer to PaysafeCard to complete the payment
    return redirect()->away($reference->url);
} catch (\Exception $e) {
    // handle exception
}
```

`$paysafeCardReferenceData` contains the normalized payment information:

```php
[
    'success' => true,
    'state' => 0,
    'response' => "OK",
    'identifier' => "order-49",
    'reference' => "000017428",
    'url' => "https://clientes.eupago.pt/paysafecard/pay/...",
    'value' => 25.00,
]
```

## Using the trait

Alternatively, use the `HasPaysafeCardReferences` trait:

```php
use CodeTech\EuPago\Traits\HasPaysafeCardReferences;

class Order extends Model
{
    use HasPaysafeCardReferences;
}
```

With the trait applied, you can create and persist a reference in a single call. It
returns the persisted reference (whose `url` you redirect to) on success, or the errors
on failure:

```php
$reference = $order->createPaysafeCardReference($value, $id, $returnUrl);
```

Retrieve the PaysafeCard references:

```php
$paysafeCardReferences = $order->paysafeCardReferences;
```

When the payment is completed, the [callback](callbacks.md) fires a `PaysafeCardReferencePaid` event.
