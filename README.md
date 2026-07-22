![Laravel Eupago](https://raw.githubusercontent.com/CodeTechAgency/laravel-eupago/master/art/banner.png)

# Laravel Eupago

[![Latest version](https://img.shields.io/github/release/CodeTechAgency/laravel-eupago?style=flat-square)](https://github.com/CodeTechAgency/laravel-eupago/releases)
[![Total downloads](https://img.shields.io/packagist/dt/codetech/laravel-eupago?style=flat-square)](https://packagist.org/packages/codetech/laravel-eupago)
[![Tests](https://img.shields.io/github/actions/workflow/status/CodeTechAgency/laravel-eupago/run-tests.yml?branch=master&style=flat-square&label=tests)](https://github.com/CodeTechAgency/laravel-eupago/actions/workflows/run-tests.yml)
[![GitHub license](https://img.shields.io/github/license/CodeTechAgency/laravel-eupago?style=flat-square)](https://github.com/CodeTechAgency/laravel-eupago/blob/master/LICENSE)

Accept the payment methods used in Portugal — Multibanco references, MB WAY, PayShop,
and PaysafeCard — in your Laravel application, through the
[Eupago](https://www.eupago.pt) payment gateway. The package covers everything from
creating a payment on any of your Eloquent models to the webhook that confirms it,
behind a simple API.

## Quick start

```bash
composer require codetech/laravel-eupago
```

Publish the migrations:

```bash
php artisan vendor:publish --provider="CodeTech\EuPago\Providers\EuPagoServiceProvider" --tag=migrations
```

Run them:

```bash
php artisan migrate
```

Set your Eupago credentials in `.env`:

```ini
EUPAGO_ENV=test
EUPAGO_API_KEY=demo-xxxx-xxxx-xxxx-xxx
EUPAGO_CHANNEL=demo
```

Add a payment method's trait to a model and create a payment — e.g. MB WAY:

```php
use CodeTech\EuPago\Traits\HasMbWayReferences;

class Order extends Model
{
    use HasMbWayReferences;
}

$order = Order::find(1);

$reference = $order->createMbwayReference($order->value, $order->id, '912345678');
```

When the customer confirms the payment, the package validates Eupago's callback, marks
the reference as paid, and fires an `MBWayReferencePaid` event.

## Documentation

This package handles the full payment lifecycle — creating references, webhooks,
status checks, refunds. To learn all about it, head over to
[the extensive documentation](https://www.codetech.pt/open-source/laravel-eupago).

Upgrading from an older version? See the [upgrade guide](https://github.com/CodeTechAgency/laravel-eupago/blob/master/UPGRADE.md).

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
