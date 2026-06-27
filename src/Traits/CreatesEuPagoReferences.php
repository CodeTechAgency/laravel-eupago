<?php

namespace CodeTech\EuPago\Traits;

use CodeTech\EuPago\EuPago;

trait CreatesEuPagoReferences
{
    /**
     * Runs the EuPago request and persists the reference on success.
     *
     * @param EuPago $payment
     * @param string $relation the relationship method that stores the reference
     * @return \Illuminate\Database\Eloquent\Model|array the persisted reference, or the errors on failure
     * @throws \Illuminate\Http\Client\ConnectionException
     * @throws \Illuminate\Http\Client\RequestException
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
