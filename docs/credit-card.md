---
title: Credit Card
weight: 9
group: Payment methods
---

Credit Card is a **redirect flow** with 3D Secure: Eupago returns the `url` of a
secure payment form that you must redirect the customer to, along with a
`form_transaction_id` and a `reference` for the payment (there is no entity). You pass your
own `id` (e.g. the order id), which Eupago echoes back in the callback as
`identificador`, the customer's email, and the three URLs the customer is forwarded to
when the payment succeeds, fails, or they press back on the form.

```php
use CodeTech\EuPago\CreditCard\CreditCard;

$order = Order::find(1);

$creditCard = new CreditCard(
    $order->value,
    $order->id,
    route('checkout.success'),
    route('checkout.fail'),
    route('checkout.back'),
    $order->customer_email,
);

try {
    $creditCardReferenceData = $creditCard->create();

    if ($creditCard->hasErrors()) {
        // handle errors
    }

    $reference = $order->creditCardReferences()->create($creditCardReferenceData);

    // Redirect the customer to the secure form to complete the payment
    return redirect()->away($reference->url);
} catch (\Exception $e) {
    // handle exception
}
```

`$creditCardReferenceData` contains the normalized payment information:

```php
[
    'success' => true,
    'status' => "Success",
    'identifier' => "order-50",
    'form_transaction_id' => "9f2c41d7b83e4a6590cd1f7e2b84a053",
    'reference' => "284137",
    'url' => "https://clientes.eupago.pt/api/extern/creditcard/form/9f2c41d7b83e4a6590cd1f7e2b84a053",
    'value' => 30.00,
]
```

`form_transaction_id` identifies the secure form. It is not the id [refunds](refunds.md) are
keyed by — that one only arrives with the callback, which stores it on the reference as
`transaction_id` once the payment is made.

The form defaults to Portuguese and Eupago emails the customer about the payment. Both
can be changed through the two optional constructor arguments, `$lang` (e.g. `'EN'`) and
`$notify`:

```php
$creditCard = new CreditCard(
    $order->value,
    $order->id,
    route('checkout.success'),
    route('checkout.fail'),
    route('checkout.back'),
    $order->customer_email,
    lang: 'EN',
    notify: false,
);
```

Eupago rejects a request it cannot serve (an invalid API key, an amount above the
3999 € maximum, …) with a code and a message, which land in the error bag:

```php
$creditCard->getErrors();

// ['APIKEY_MISSING' => 'API Key was not available in the request']
```

## Using the trait

Alternatively, use the `HasCreditCardReferences` trait:

```php
use CodeTech\EuPago\Traits\HasCreditCardReferences;

class Order extends Model
{
    use HasCreditCardReferences;
}
```

With the trait applied, you can create and persist a reference in a single call. It
returns the persisted reference (whose `url` you redirect to) on success, or the errors
on failure:

```php
$reference = $order->createCreditCardReference($value, $id, $successUrl, $failUrl, $backUrl, $email);
```

Retrieve the Credit Card references:

```php
$creditCardReferences = $order->creditCardReferences;
```

When the payment is completed, the [callback](callbacks.md) fires a `CreditCardReferencePaid`
event and stores the Eupago transaction on the reference, so a paid reference can be
[refunded](refunds.md) through `$reference->transaction_id`.

## Testing on the sandbox

Eupago provides a test card for the sandbox environment. The form asks for a one-time
password before it completes, where `0101` approves and `3333` rejects the payment:

| Card scheme | Card number        | Expiry              | CVV                  |
|-------------|--------------------|---------------------|----------------------|
| Visa        | `4018810000150015` | any future date     | any three digits     |
