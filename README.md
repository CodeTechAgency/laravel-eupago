![laravel-eupago-repo-banner](https://user-images.githubusercontent.com/17640929/161711354-03727be1-88cb-49df-ad58-494a61606e0b.png)


# Laravel EuPago

A Laravel package for making payments through the EuPago API.

[![Latest version](https://img.shields.io/github/release/CodeTechAgency/laravel-eupago?style=flat-square)](https://github.com/CodeTechAgency/laravel-eupago/releases)
[![GitHub license](https://img.shields.io/github/license/CodeTechAgency/laravel-eupago?style=flat-square)](https://github.com/CodeTechAgency/laravel-eupago/blob/master/LICENSE)


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


### MB References

#### Usage

For creating a MB reference, take the following example:
```
use CodeTech\EuPago\MB\MB;

$order = Order::find(1);

$mb = new MB(
    $order->value,
    $order->id,
    $order->date,
    $order->payment_limit_date,
    $order->value,
    $order->value,
    0 // allows duplicated payments
);

try {
    // Make the request to EUPago's API
    $mbReferenceData = $mb->create();

    if ($mb->hasErrors()) {
        // handle errors
    }
    
    // Make the request to EUPago's API
    $order->mbReferences()->create($mbReferenceData);
} catch (\Exception $e) {
    // handle exception
}
```

`$referenceData` will contain all the information about the payment: 
```
[
    'success' => true,
    'state' => 0,
    'response' => "OK",
    'reference' => "000001236",
    'value' => "3.00000",
]
```

Use the trait on the models for which you want to generate MB references:

```

use CodeTech\EuPago\Traits\Mbable;

class Order extends Model
{
    use Mbable;

```

Retrieve the MB references:

```
$order = Order::find(1);

$mbReferences = $order->mbReferences;
```

#### Callback

The package already handles the callback, updating the payment reference state and triggering an `MBWayReferencePaid` event.

```
GET

/eupago/mb/callback
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


### MB Way References

#### Usage

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

#### Callback

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



---


## License

**codetech/laravel-eupago** is open-sourced software licensed under the [MIT license](https://github.com/CodeTechAgency/laravel-eupago/blob/master/LICENSE).


## About CodeTech

[CodeTech](https://www.codetech.pt) is a web development agency based on Matosinhos, Portugal. Oh, and we LOVE Laravel!
