<?php

namespace CodeTech\EuPago\Traits;

use CodeTech\EuPago\Models\PaysafeCardReference;
use CodeTech\EuPago\PaysafeCard\PaysafeCard;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;

trait HasPaysafeCardReferences
{
    use CreatesEuPagoReferences;

    /**
     * Get all of the model's PaysafeCard references.
     */
    public function paysafeCardReferences()
    {
        return $this->morphMany(PaysafeCardReference::class, 'paysafecardable');
    }

    /**
     * Creates and persists a PaysafeCard reference.
     *
     * PaysafeCard is a redirect flow: on success the persisted reference holds
     * the payment `url` the customer must be redirected to.
     *
     * @return Model|array the persisted reference, or the errors on failure
     *
     * @throws ConnectionException
     * @throws RequestException
     */
    public function createPaysafeCardReference(float $value, string $id, ?string $returnUrl = null)
    {
        return $this->persistReference(
            new PaysafeCard($value, $id, $returnUrl),
            'paysafeCardReferences'
        );
    }
}
