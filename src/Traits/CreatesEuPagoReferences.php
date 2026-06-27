<?php

namespace CodeTech\EuPago\Traits;

use CodeTech\EuPago\EuPago;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;

trait CreatesEuPagoReferences
{
    /**
     * Runs the EuPago request and persists the reference on success.
     *
     * @param  string  $relation  the relationship method that stores the reference
     * @return Model|array the persisted reference, or the errors on failure
     *
     * @throws ConnectionException
     * @throws RequestException
     */
    protected function persistReference(EuPago $payment, string $relation)
    {
        $referenceData = $payment->create();

        if ($payment->hasErrors()) {
            return $payment->getErrors();
        }

        return $this->{$relation}()->create($referenceData);
    }
}
