<?php

namespace CodeTech\EuPago\Traits;

use CodeTech\EuPago\Models\PaysafecardReference;
use CodeTech\EuPago\Paysafecard\Paysafecard;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;

trait Paysafecardable
{
    use CreatesEuPagoReferences;

    /**
     * Get all of the model's Paysafecard references.
     */
    public function paysafecardReferences()
    {
        return $this->morphMany(PaysafecardReference::class, 'paysafecardable');
    }

    /**
     * Creates and persists a Paysafecard reference.
     *
     * Paysafecard is a redirect flow: on success the persisted reference holds
     * the payment `url` the customer must be redirected to.
     *
     * @return Model|array the persisted reference, or the errors on failure
     *
     * @throws ConnectionException
     * @throws RequestException
     */
    public function createPaysafecardReference(float $value, string $id, ?string $returnUrl = null)
    {
        return $this->persistReference(
            new Paysafecard($value, $id, $returnUrl),
            'paysafecardReferences'
        );
    }
}
