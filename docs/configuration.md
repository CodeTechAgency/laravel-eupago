---
title: Configuration
weight: 4
---

The package is configured through environment variables (see `config/eupago.php`):

```ini
EUPAGO_ENV=test
EUPAGO_API_KEY=demo-xxxx-xxxx-xxxx-xxx
EUPAGO_CHANNEL=demo
EUPAGO_ROUTES=true
EUPAGO_CLIENT_ID=
EUPAGO_CLIENT_SECRET=
```

## Environment

Two environments are available: `test` and `prod`. Use `test` (the Eupago sandbox,
`sandbox.eupago.pt`) while developing, and switch to `prod` (`clientes.eupago.pt`)
when your application is ready to take real payments.

## API key and channel

`EUPAGO_API_KEY` is the API key of your Eupago channel — you find it in the
[Eupago backoffice](https://clientes.eupago.pt), where each channel has its own key.
`EUPAGO_CHANNEL` is the channel name; incoming callbacks are validated against it.

## OAuth client credentials

Eupago's management API — currently used for [refunds](refunds.md) — is authenticated with
OAuth 2.0 bearer tokens instead of the API key. Generate the client credentials in the
Eupago backoffice and set `EUPAGO_CLIENT_ID` and `EUPAGO_CLIENT_SECRET`. The package
requests tokens through the client credentials grant and caches them until they expire,
so you never handle tokens yourself. If you only create references and query their
status, you can leave these empty.

## Routes

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
