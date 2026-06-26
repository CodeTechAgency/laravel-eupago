![laravel-eupago-repo-banner](art/banner.png)


# Laravel EuPago

A Laravel package for making payments through the EuPago API.

[![Tests](https://img.shields.io/github/actions/workflow/status/CodeTechAgency/laravel-eupago/run-tests.yml?branch=master&style=flat-square&label=tests)](https://github.com/CodeTechAgency/laravel-eupago/actions/workflows/run-tests.yml)
[![Latest version](https://img.shields.io/github/release/CodeTechAgency/laravel-eupago?style=flat-square)](https://github.com/CodeTechAgency/laravel-eupago/releases)
[![GitHub license](https://img.shields.io/github/license/CodeTechAgency/laravel-eupago?style=flat-square)](https://github.com/CodeTechAgency/laravel-eupago/blob/master/LICENSE)


## Compatibility

| Laravel | Package |
|---------|---------|
| 11      | `v3.1+` |
| 10      | `v2.1+` |
| 9       | `v2.x`  |
| 8       | `v1.x`  |


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



### PayShop References

#### Usage

For creating a PayShop reference, take the following example:
```
use CodeTech\EuPago\PayShop\PayShop;

$order = Order::find(1);

$payShop = new PayShop(
    $order->value,
    $order->id
);

try {
    // Make the request to EUPago's API
    $payShopReferenceData = $payShop->create();

    if ($payShop->hasErrors()) {
        // handle errors
    }

    $order->payShopReferences()->create($payShopReferenceData);
} catch (\Exception $e) {
    // handle exception
}
```

`$payShopReferenceData` will contain all the information about the payment:
```
[
    'success' => true,
    'state' => 0,
    'response' => "OK",
    'reference' => 1800000132722,
    'value' => "10.00000",
]
```

Use the trait on the models for which you want to generate PayShop references:

```

use CodeTech\EuPago\Traits\PayShopable;

class Order extends Model
{
    use PayShopable;

```

Retrieve the PayShop references:

```
$order = Order::find(1);

$payShopReferences = $order->payShopReferences;
```

#### Callback

The package already handles the callback, updating the payment reference state and triggering a `PayShopReferencePaid` event.

```
GET

/eupago/payshop/callback
```

#### Params

| Name          | Type      |
|---------------|:---------:|
| valor         | float     |
| canal         | string    |
| referencia    | string    |
| transacao     | string    |
| identificador | string    |
| mp            | string    |
| chave_api     | string    |
| data          | date time |
| entidade      | string    |
| comissao      | float     |
| local         | string    |


---


## Upgrading

Please see [UPGRADE.md](https://github.com/CodeTechAgency/laravel-eupago/blob/master/UPGRADE.md) for information on how to upgrade between major versions.


## License

**codetech/laravel-eupago** is open-sourced software licensed under the [MIT license](https://github.com/CodeTechAgency/laravel-eupago/blob/master/LICENSE).


## About CodeTech

[CodeTech](https://www.codetech.pt) is a web development agency based on Matosinhos, Portugal. Oh, and we LOVE Laravel!
