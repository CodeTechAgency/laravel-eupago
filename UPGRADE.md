# Upgrading

## From v3.1.x to v3.2.0

This release adds PayShop support, which uses a new `payshop_references` table. Re-publish the migrations (existing files are left untouched) and run the new one:

```bash
php artisan vendor:publish --provider=CodeTech\EuPago\Providers\EuPagoServiceProvider --tag=migrations
php artisan migrate
```

## From v2.x to v3.0.0

This release drops support for Laravel 9.x (and PHP 8.0). Make sure your application runs Laravel 10.x or higher on PHP 8.1+. No changes to your application code are required.

## From v1.x to v2.0.0

This release drops support for PHP 7.x and Laravel 8.x. No changes to your application code are required.

## From v1.0.x to v1.1.0

This release adds Multibanco reference support, which uses a new `mb_references` table. Re-publish the migrations (existing files are left untouched) and run the new one:

```bash
php artisan vendor:publish --provider=CodeTech\EuPago\Providers\EuPagoServiceProvider --tag=migrations
php artisan migrate
```
