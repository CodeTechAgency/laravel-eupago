---
title: Installation
weight: 3
group: Getting started
---

Add the package to your Laravel application using Composer:

```bash
composer require codetech/laravel-eupago
```

The service provider is registered automatically via package discovery.

Publish the migrations:

```bash
php artisan vendor:publish --provider="CodeTech\EuPago\Providers\EuPagoServiceProvider" --tag=migrations
```

Run them:

```bash
php artisan migrate
```

Optionally, publish the configuration file:

```bash
php artisan vendor:publish --provider="CodeTech\EuPago\Providers\EuPagoServiceProvider" --tag=config
```

And the translations:

```bash
php artisan vendor:publish --provider="CodeTech\EuPago\Providers\EuPagoServiceProvider" --tag=translations
```
