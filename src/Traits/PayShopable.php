<?php

namespace CodeTech\EuPago\Traits;

use CodeTech\EuPago\Models\PayShopReference;
use CodeTech\EuPago\PayShop\PayShop;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;

trait PayShopable
{
    use CreatesEuPagoReferences;

    /**
     * Get all of the model's PayShop references.
     */
    public function payShopReferences()
    {
        return $this->morphMany(PayShopReference::class, 'payshopable');
    }

    /**
     * Creates and persists a PayShop reference.
     *
     * @return Model|array the persisted reference, or the errors on failure
     *
     * @throws ConnectionException
     * @throws RequestException
     */
    public function createPayShopReference(float $value, string $id)
    {
        return $this->persistReference(
            new PayShop($value, $id),
            'payShopReferences'
        );
    }
}
