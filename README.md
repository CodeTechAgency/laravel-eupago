![Laravel Eupago](https://raw.githubusercontent.com/CodeTechAgency/laravel-eupago/master/art/banner.png)

# Laravel Eupago

[![Latest version](https://img.shields.io/github/release/CodeTechAgency/laravel-eupago?style=flat-square)](https://github.com/CodeTechAgency/laravel-eupago/releases)
[![Total downloads](https://img.shields.io/packagist/dt/codetech/laravel-eupago?style=flat-square)](https://packagist.org/packages/codetech/laravel-eupago)
[![Tests](https://img.shields.io/github/actions/workflow/status/CodeTechAgency/laravel-eupago/run-tests.yml?branch=master&style=flat-square&label=tests)](https://github.com/CodeTechAgency/laravel-eupago/actions/workflows/run-tests.yml)
[![GitHub license](https://img.shields.io/github/license/CodeTechAgency/laravel-eupago?style=flat-square)](https://github.com/CodeTechAgency/laravel-eupago/blob/master/LICENSE)

[Eupago](https://www.eupago.pt) is a Portuguese payment gateway that lets businesses accept
the payment methods used in Portugal — Multibanco references, MB WAY, PayShop, and more —
through a single API. This package integrates that gateway into Laravel, end to end:

- **Create payments** for Multibanco (MB), MB WAY, PayShop, and PaysafeCard.
- **Persist payment references** as Eloquent models, attached to any model of yours (an
  `Order`, an `Invoice`, …) through ready-made traits.
- **Handle Eupago's webhooks** out of the box: the package ships the callback endpoints,
  validates the payload, marks the reference as paid, and fires an event
  (`MBReferencePaid`, `MBWayReferencePaid`, `PayShopReferencePaid`,
  `PaysafeCardReferencePaid`) you can hook your business logic on.
- **Query a reference's status** on demand, for reconciliation or missed callbacks.

You can use it as a **full integration** (traits, models, webhooks) or as a **thin API
client** (payment classes only) — see [Routes](#routes) for how to switch.

## Table of contents

- [Requirements](#requirements)
- [Installation](#installation)
- [Configuration](#configuration)
- [Usage](#usage)
  - [Multibanco (MB)](#multibanco-mb)
  - [MB WAY](#mb-way)
  - [PayShop](#payshop)
  - [PaysafeCard](#paysafecard)
  - [Callbacks](#callbacks)
- [Querying reference status](#querying-reference-status)
- [Refunds](#refunds)
- [Testing & code quality](#testing--code-quality)
- [Upgrading](#upgrading)
- [Changelog](#changelog)
- [Contributing](#contributing)
- [Security](#security)
- [Support](#support)
- [License](#license)

## Requirements

| Package version                                                          | Laravel | PHP     | Status         |
|--------------------------------------------------------------------------|---------|---------|----------------|
| 3.x (`master`)                                                           | 10 – 13 | ≥ 8.1   | Active         |
| 2.x ([`2.x`](https://github.com/CodeTechAgency/laravel-eupago/tree/2.x)) | 9 / 10  | ≥ 8.0.2 | Security fixes |
| 1.x ([`1.x`](https://github.com/CodeTechAgency/laravel-eupago/tree/1.x)) | 8       | ≥ 8.0   | End of life    |

Upgrading from an older version? See the [upgrade guide](https://github.com/CodeTechAgency/laravel-eupago/blob/master/UPGRADE.md).

## Installation

Add the package to your Laravel application using Composer:

```bash
composer require codetech/laravel-eupago
```

The service provider is registered automatically via package discovery.

Publish and run the migrations:

```bash
php artisan vendor:publish --provider="CodeTech\EuPago\Providers\EuPagoServiceProvider" --tag=migrations
php artisan migrate
```

Optionally, publish the configuration file and the translations:

```bash
php artisan vendor:publish --provider="CodeTech\EuPago\Providers\EuPagoServiceProvider" --tag=config
php artisan vendor:publish --provider="CodeTech\EuPago\Providers\EuPagoServiceProvider" --tag=translations
```

## Configuration

The package is configured through environment variables (see `config/eupago.php`):

```ini
EUPAGO_ENV=test
EUPAGO_API_KEY=demo-xxxx-xxxx-xxxx-xxx
EUPAGO_CHANNEL=demo
EUPAGO_ROUTES=true
EUPAGO_CLIENT_ID=
EUPAGO_CLIENT_SECRET=
```

### Environment

Two environments are available: `test` and `prod`. Use `test` (the Eupago sandbox,
`sandbox.eupago.pt`) while developing, and switch to `prod` (`clientes.eupago.pt`)
when your application is ready to take real payments.

### API key and channel

`EUPAGO_API_KEY` is the API key of your Eupago channel — you find it in the
[Eupago backoffice](https://clientes.eupago.pt), where each channel has its own key.
`EUPAGO_CHANNEL` is the channel name; incoming callbacks are validated against it.

### OAuth client credentials

Eupago's management API — currently used for [refunds](#refunds) — is authenticated with
OAuth 2.0 bearer tokens instead of the API key. Generate the client credentials in the
Eupago backoffice and set `EUPAGO_CLIENT_ID` and `EUPAGO_CLIENT_SECRET`. The package
requests tokens through the client credentials grant and caches them until they expire,
so you never handle tokens yourself. If you only create references and query their
status, you can leave these empty.

### Routes

The package supports two levels of usage:

- **Full integration** (default): use the traits and models to persist references, and let the
  package handle Eupago's webhooks — it registers the callback routes (`/eupago/*/callback`)
  automatically.
- **Thin API client**: use only the payment classes (e.g. `new MB(...)->create()`) and handle
  persistence and webhooks yourself.

If you only need the thin client, disable the automatic route registration:

```ini
EUPAGO_ROUTES=false
```

With the routes disabled you can still mount the package's callback controllers on routes of
your own, giving you full control over the path and middleware:

```php
use CodeTech\EuPago\Http\Controllers\MBController;

Route::get('webhooks/eupago/mb', [MBController::class, 'callback'])
    ->middleware('web')
    ->name('eupago.mb.callback');
```

> **Note:** if your application caches routes, run `php artisan route:clear` after changing
> this setting.

## Usage

Every payment method follows the same pattern: build the payment object, call `create()`
to run the request against the Eupago API, and persist the returned reference data — or
let the method's trait do the create-and-persist in a single call. When Eupago confirms
the payment, the package [handles the callback](#callbacks) and fires the method's
`*ReferencePaid` event.

### Multibanco (MB)

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

When the reference is paid, the callback fires an `MBReferencePaid` event.

### MB WAY

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

When the payment is confirmed, the callback fires an `MBWayReferencePaid` event.

### PayShop

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

When the reference is paid, the callback fires a `PayShopReferencePaid` event.

### PaysafeCard

Unlike the reference-based methods above, PaysafeCard is a **redirect flow**: Eupago
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

When the payment is completed, the callback fires a `PaysafeCardReferencePaid` event.

### Callbacks

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

## Querying reference status

Besides the callback, you can query a reference's current status on demand — useful
for reconciliation or when a callback is missed or delayed. Eupago resolves any
reference type (MB, MB WAY, PayShop) through a single reference-info endpoint, so
the same call works regardless of how the reference was created:

```php
use CodeTech\EuPago\EuPago;

$eupago = new EuPago;

try {
    $status = $eupago->status($reference, $entity);

    if ($eupago->hasErrors()) {
        // handle errors
    }
} catch (\Exception $e) {
    // handle exception
}
```

The `$entity` argument is optional. `$status` is mapped to normalized keys, where
`reference_state` holds the payment status (e.g. `"pendente"`, `"pago"`):

```php
[
    'success' => true,
    'state' => 0,
    'response' => "OK",
    'entity' => "81921",
    'reference' => "800152011",
    'identifier' => "order-123",
    'reference_state' => "pendente",
    'created_date' => "2026-06-27",
    'created_time' => "00:22:49",
    'archived' => false,
]
```

## Refunds

Paid transactions can be refunded, partially or in full, through Eupago's management
API. Refunds require the [OAuth client credentials](#oauth-client-credentials) to be
configured. The refund is keyed by the transaction id — the `transacao` value delivered
by the payment callback:

```php
use CodeTech\EuPago\EuPago;

$eupago = new EuPago;

try {
    $result = $eupago->refund($transactionId, 10.50);

    if ($eupago->hasErrors()) {
        // handle rejection (e.g. refund larger than the payment)
    }
} catch (\Exception $e) {
    // handle exception
}
```

`refund()` also accepts an optional reason and, for payment methods without a direct
refund path, the destination bank account:

```php
$eupago->refund($transactionId, 10.50, reason: 'duplicate order', iban: 'PT50...', bic: 'BPOTPTPL');
```

The result is mapped to normalized keys:

```php
[
    'success' => true,
    'status' => "Success",
    'refund_id' => "12345",
    'code' => null,
    'text' => null,
]
```

When Eupago rejects the refund (`status` `"Rejected"`), the rejection's `code` and `text`
are also added to the error bag. Transport and server errors throw, mirroring `create()`
and `status()`.

## Testing & code quality

Run the test suite, static analysis, and code-style checks via Composer:

```bash
composer test      # Pest test suite
composer analyse   # PHPStan/Larastan static analysis
composer lint      # Pint code-style check (run `composer format` to fix)
```

## Upgrading

Please see [UPGRADE.md](https://github.com/CodeTechAgency/laravel-eupago/blob/master/UPGRADE.md)
for information on how to upgrade between versions.

## Changelog

Every release is documented on the [GitHub releases page](https://github.com/CodeTechAgency/laravel-eupago/releases).

## Contributing

Contributions are welcome! Please read the [contributing guidelines](https://github.com/CodeTechAgency/laravel-eupago/blob/master/CONTRIBUTING.md) before opening an issue or pull request.

## Security

If you discover a security vulnerability, please follow the [security policy](https://github.com/CodeTechAgency/laravel-eupago/blob/master/SECURITY.md) — do not report it publicly.

## Support

If this package helps you, consider [starring the repository](https://github.com/CodeTechAgency/laravel-eupago) —
it helps other developers discover it.

---

## License

**codetech/laravel-eupago** is open-sourced software licensed under
the [MIT license](https://github.com/CodeTechAgency/laravel-eupago/blob/master/LICENSE).

## About CodeTech

[CodeTech](https://www.codetech.pt) is a web development agency based in Matosinhos, Portugal. Oh, and we LOVE Laravel!
