# Laravel EuPago

A Laravel package for making payments through the EuPago API.


## Installation


Install the PHP dependency
```
composer require codetech/laravel-eupago
```

Publish the migration
```
php artisan vendor:publish --provider=CodeTech\\EuPago\\Providers\\EuPagoServiceProvider --tag=migrations
```

Run the migration
```
php artisan migrate
```

Publish the configuration file (optional)
```
php artisan vendor:publish --provider=CodeTech\\EuPago\\Providers\\EuPagoServiceProvider --tag=config
```

Publish the translations files (optional)
```
php artisan vendor:publish --provider=CodeTech\\EuPago\\Providers\\EuPagoServiceProvider --tag=translations
```


## Configurations

### Environment

There are two environments available for you to use: "test" and "prod". As you may have guessed,
you can use the "test" environment during the development stage of your application. Switch to "prod"
environment when your application is ready for production.


## Usage

Use the trait on the models for which you want to generate MB Way references:

```

use CodeTech\EuPago\Traits\Mbwayable;

class Order extends Model
{
    use Mbwayable;

```


Retrieve the MB Way references:

```
$order = Order::find(1);

$mbwayReferences = $order->mbwayReferences;
```

## MBWay

### Callback

The package already handles the callback, updating the payment reference state and triggering an `MBWayReferencePaid` event.

```
GET

/eupago/mbway/callback
```

####Params

| Name          | Type      |
|---------------|:---------:|
| valor         | float     |
| canal         | string    |
| referencia    | string    |
| transacao     | string    |
| identificador | integer   |
| mp            | string    |
| chave_api     | string    |
| data          | date time |
| entidade      | string    |
| comissao      | float     |
| local         | string    |
