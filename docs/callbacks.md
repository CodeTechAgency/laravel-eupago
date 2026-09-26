---
title: Callbacks
weight: 10
group: Handling payments
---

Eupago notifies your application of confirmed payments through a webhook. The
[Eupago backoffice](https://clientes.eupago.pt) takes a single notification URL per channel and
sends every payment method's notification to it, so set it to the package's callback endpoint:

```
https://your-app.test/eupago/callback
```

The endpoint reads the payment method from the notification (`mp`) and handles it:

| Payment method | `mp`    | Event fired                |
|----------------|---------|----------------------------|
| Multibanco     | `PC:PT` | `MBReferencePaid`          |
| MB WAY         | `MW:PT` | `MBWayReferencePaid`       |
| PayShop        | `PS:PT` | `PayShopReferencePaid`     |
| PaysafeCard    | `PF:PT` | `PaysafeCardReferencePaid` |
| Credit Card    | `CC:PT` | `CreditCardReferencePaid`  |

The callback first checks the channel and API key, then matches the reference against the
values Eupago echoes back, marks it as paid and fires the method's event with the reference as
payload. The event fires once the payment is stored, and only once per payment: a notification
Eupago delivers again is acknowledged without firing it. That also holds when a listener throws,
so put work that can fail, such as calling another service, in a
[queued listener](https://laravel.com/docs/events#queued-event-listeners), which the queue retries.

Multibanco references can accept an amount range and allow repeat payments, so each payment is
recorded on its own, and the `MBReferencePaid` event carries it next to the reference. With an
amount range, the payment can be less than the reference's value, so compare the amount paid
with what is owed:

```php
public function handle(MBReferencePaid $event): void
{
    $event->payment?->value;          // the amount paid
    $event->payment?->transaction_id; // the Eupago transaction
}
```

The payment is null when you dispatch the event yourself without one.

The per-method endpoints of earlier versions — `/eupago/mb/callback`, `/eupago/mbway/callback`,
`/eupago/payshop/callback`, `/eupago/paysafecard/callback` and `/eupago/creditcard/callback` — are
deprecated and will be removed in v4. They are now aliases of `/eupago/callback`, so a URL already
set in the backoffice keeps working for every payment method, but switch it to
`/eupago/callback` before upgrading to v4.

The callback receives these query parameters:

| Name          | Type                          | Required |
|---------------|-------------------------------|:--------:|
| valor         | float                         | yes      |
| canal         | string                        | yes      |
| referencia    | string                        | yes      |
| transacao     | string                        | yes      |
| identificador | string                        | yes      |
| mp            | string                        | yes      |
| chave_api     | string                        | yes      |
| data          | date time (`Y-m-d:H:i:s`)     | yes      |
| entidade      | string                        | yes      |
| comissao      | float                         | yes      |
| local         | string                        | no       |

To mount the callback controller on a route of your own instead of the automatically
registered ones, see [Routes](configuration.md#routes).
