---
title: Installation
weight: 3
---

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
