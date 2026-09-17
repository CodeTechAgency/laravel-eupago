<?php

namespace CodeTech\EuPago\Traits;

use CodeTech\EuPago\CreditCard\CreditCard;
use CodeTech\EuPago\Models\CreditCardReference;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;

trait HasCreditCardReferences
{
    use CreatesEuPagoReferences;

    /**
     * Get all of the model's Credit Card references.
     */
    public function creditCardReferences()
    {
        return $this->morphMany(CreditCardReference::class, 'creditcardable');
    }

    /**
     * Creates and persists a Credit Card reference.
     *
     * Credit Card is a redirect flow: on success the persisted reference holds
     * the `url` of the secure payment form the customer must be redirected to.
     *
     * @return Model|array the persisted reference, or the errors on failure
     *
     * @throws ConnectionException
     * @throws RequestException
     */
    public function createCreditCardReference(
        float $value,
        string $id,
        string $successUrl,
        string $failUrl,
        string $backUrl,
        string $email,
        ?string $lang = null,
        bool $notify = true
    ) {
        return $this->persistReference(
            new CreditCard($value, $id, $successUrl, $failUrl, $backUrl, $email, $lang, $notify),
            'creditCardReferences'
        );
    }
}
