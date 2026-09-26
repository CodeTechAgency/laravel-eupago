# Upgrading

## From v3.9.x to v3.10.0

The publish tags are now prefixed with the package name: `eupago-migrations`, `eupago-config` and `eupago-translations`. The old `migrations`, `config` and `translations` tags keep working.

On MySQL and MariaDB, the migrations for the `paysafecard_references` and `credit_card_references` tables used to fail with "Identifier name ... is too long", leaving the table created but the migration unrecorded. If that happened to you, drop the table the error names:

```bash
php artisan tinker --execute="Schema::dropIfExists('paysafecard_references')"
```

Delete the published copy of that migration, since publishing never overwrites an existing file:

```bash
rm database/migrations/2026_06_29_000000_create_paysafecard_references_table.php
```

Re-publish the migrations to get the fixed version:

```bash
php artisan vendor:publish --provider=CodeTech\\EuPago\\Providers\\EuPagoServiceProvider --tag=eupago-migrations
```

Run the migrations:

```bash
php artisan migrate
```

If the error named `credit_card_references` instead, repeat the same steps with that table and `2026_09_05_000000_create_credit_card_references_table.php`.

Translations used to be published to `resources/lang/vendor/eupago`. Creating `resources/lang` makes Laravel use it as the app's lang path, so an app that keeps its translations in `lang/` stopped loading them. If you published the translations, create the new location:

```bash
mkdir -p lang/vendor
```

Move the package's translations there:

```bash
mv resources/lang/vendor/eupago lang/vendor/eupago
```

If `resources/lang` is now empty, remove it so Laravel goes back to `lang/`:

```bash
rmdir resources/lang/vendor resources/lang
```

Eupago sends every payment method's notification to the single URL a channel takes, so the per-method callback endpoints used to confirm only the payments of the method whose URL was set. There is now a single endpoint, and the per-method ones are aliases of it, so every method is confirmed whichever URL is set. The per-method endpoints are deprecated and will be removed in v4, so in the Eupago backoffice, set the channel's notification URL to the new endpoint:

```
https://your-app.test/eupago/callback
```

The per-method routes now point at `CallbackController`, so build their URLs by route name (`route('eupago.mb.callback')`) rather than by controller action.

If you disabled the package routes and mounted a per-method controller on a route of your own, it still confirms only that method. Point your route at `CallbackController` instead, keeping its path so the URL set in the backoffice keeps working, and take it out of the `web` middleware group, which stores the URL — API key included — in the session:

```php
Route::get('webhooks/eupago/mb', [CallbackController::class, 'callback'])
    ->withoutMiddleware('web')
    ->name('eupago.mb.callback');
```

The paid events now fire once the payment is stored, so queued listeners always find it. A notification Eupago delivers again now gets a 200 instead of a 404, still without firing the event.

Multibanco payments are now recorded in a table of their own, so references with an amount range or with repeat payments can be confirmed. Re-publish the migrations, which leaves the existing files untouched:

```bash
php artisan vendor:publish --provider=CodeTech\\EuPago\\Providers\\EuPagoServiceProvider --tag=eupago-migrations
```

Run the new migration, which also records the payment of every reference paid since v3.9.0, when the transaction started being stored:

```bash
php artisan migrate
```

A Multibanco reference with an amount range is now confirmed for any amount within it, so the payment can be less than the reference's value. If your references accept a range, compare `$event->payment?->value` in your `MBReferencePaid` listeners with the amount owed.

## From v3.8.x to v3.9.0

Multibanco, MB WAY, PayShop and PaysafeCard references now store the Eupago transaction their callback delivers, so a paid reference can be refunded through `$reference->transaction_id`. A new migration adds the column. Re-publish the migrations, which leaves the existing files untouched:

```bash
php artisan vendor:publish --provider=CodeTech\\EuPago\\Providers\\EuPagoServiceProvider --tag=migrations
```

Run the new migration:

```bash
php artisan migrate
```

References paid before the upgrade keep a null `transaction_id` — the value only ever exists in the callback payload, so there is nothing to backfill from.

## From v3.7.x to v3.8.0

This release adds Credit Card support, which uses a new `credit_card_references` table. Re-publish the migrations (existing files are left untouched) and run the new one:

```bash
php artisan vendor:publish --provider=CodeTech\\EuPago\\Providers\\EuPagoServiceProvider --tag=migrations
php artisan migrate
```

## From v3.5.x to v3.6.0

The `*able` traits are deprecated in favour of `Has*References` names (matching `HasPaysafeCardReferences`). The old names keep working as aliases until v4 — no behavior change — but you should update your models:

| Deprecated | Use instead |
|------------|-------------|
| `Mbable` | `HasMultibancoReferences` |
| `Mbwayable` | `HasMbWayReferences` |
| `PayShopable` | `HasPayShopReferences` |

The `mbway_references.value` column changes from FLOAT to DECIMAL(10,2) so callback matching can never miss a payment due to float rounding. Re-publish the migrations and run the new one:

```bash
php artisan vendor:publish --provider=CodeTech\\EuPago\\Providers\\EuPagoServiceProvider --tag=migrations
php artisan migrate
```

> **Laravel 10 apps only:** the column-change migration needs `doctrine/dbal` (`composer require doctrine/dbal`). Laravel 11+ changes columns natively.

## From v3.4.x to v3.5.0

This release adds PaysafeCard support, which uses a new `paysafecard_references` table. Re-publish the migrations (existing files are left untouched) and run the new one:

```bash
php artisan vendor:publish --provider=CodeTech\\EuPago\\Providers\\EuPagoServiceProvider --tag=migrations
php artisan migrate
```

## From v3.1.x to v3.2.0

This release adds PayShop support, which uses a new `payshop_references` table. Re-publish the migrations (existing files are left untouched) and run the new one:

```bash
php artisan vendor:publish --provider=CodeTech\\EuPago\\Providers\\EuPagoServiceProvider --tag=migrations
php artisan migrate
```

## From v2.x to v3.0.0

This release drops support for Laravel 9.x (and PHP 8.0). Make sure your application runs Laravel 10.x or higher on PHP 8.1+. No changes to your application code are required.

## From v1.x to v2.0.0

This release drops support for PHP 7.x and Laravel 8.x. No changes to your application code are required.

## From v1.0.x to v1.1.0

This release adds Multibanco reference support, which uses a new `mb_references` table. Re-publish the migrations (existing files are left untouched) and run the new one:

```bash
php artisan vendor:publish --provider=CodeTech\\EuPago\\Providers\\EuPagoServiceProvider --tag=migrations
php artisan migrate
```
