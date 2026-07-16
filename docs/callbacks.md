---
title: Callbacks
weight: 9
---

Eupago notifies your application of confirmed payments through a webhook — configure the
callback URL in the [Eupago backoffice](https://clientes.eupago.pt) for your channel.
The package registers one endpoint per payment method:

| Payment method | Callback endpoint             | Event fired              |
|----------------|-------------------------------|--------------------------|
| Multibanco     | `GET /eupago/mb/callback`     | `MBReferencePaid`        |
| MB WAY         | `GET /eupago/mbway/callback`  | `MBWayReferencePaid`     |
| PayShop        | `GET /eupago/payshop/callback`| `PayShopReferencePaid`   |
| PaysafeCard    | `GET /eupago/paysafecard/callback` | `PaysafeCardReferencePaid` |

Each callback validates the payload (including the channel and API key), matches the
pending reference on `referencia` and value, marks it as paid, and fires the
corresponding event with the reference as payload.

All callbacks receive the same query parameters:

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

To mount the callback controllers on routes of your own instead of the automatically
registered ones, see [Routes](configuration.md#routes).
