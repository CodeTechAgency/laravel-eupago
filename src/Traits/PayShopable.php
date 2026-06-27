<?php

namespace CodeTech\EuPago\Traits;

use CodeTech\EuPago\PayShop\PayShop;
use CodeTech\EuPago\Models\PayShopReference;

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
     * @param float $value
     * @param string $id
     * @return \Illuminate\Database\Eloquent\Model|array the persisted reference, or the errors on failure
     * @throws \Illuminate\Http\Client\ConnectionException
     * @throws \Illuminate\Http\Client\RequestException
     */
    public function createPayShopReference(float $value, string $id)
    {
        return $this->persistReference(
            new PayShop($value, $id),
            'payShopReferences'
        );
    }
}
