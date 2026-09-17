<?php

namespace CodeTech\EuPago\CreditCard;

use CodeTech\EuPago\EuPago;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;

class CreditCard extends EuPago
{
    /**
     * The unique resource identifier.
     */
    const URI = '/api/v1.02/creditcard/create';

    /**
     * The payment value.
     *
     * @var float
     */
    protected $value;

    /**
     * External identifier. Ex: the order id.
     *
     * @var string
     */
    protected $id;

    /**
     * The URL the customer is forwarded to when the payment succeeds.
     *
     * @var string
     */
    protected $successUrl;

    /**
     * The URL the customer is forwarded to when the payment fails.
     *
     * @var string
     */
    protected $failUrl;

    /**
     * The URL the customer is forwarded to when they press back on the form.
     *
     * @var string
     */
    protected $backUrl;

    /**
     * The customer's email.
     *
     * @var string
     */
    protected $email;

    /**
     * The language of the payment form. Eupago defaults to PT.
     *
     * @var string|null
     */
    protected $lang;

    /**
     * Whether Eupago notifies the customer by email.
     *
     * @var bool
     */
    protected $notify;

    /**
     * CreditCard constructor.
     */
    public function __construct(
        float $value,
        string $id,
        string $successUrl,
        string $failUrl,
        string $backUrl,
        string $email,
        ?string $lang = null,
        bool $notify = true
    ) {
        $this->value = $value;
        $this->id = $id;
        $this->successUrl = $successUrl;
        $this->failUrl = $failUrl;
        $this->backUrl = $backUrl;
        $this->email = $email;
        $this->lang = $lang;
        $this->notify = $notify;
    }

    /**
     * Creates the hosted payment form.
     *
     * Credit Card lives on the v1.02 API: a JSON request authenticated by the
     * API key in the Authorization header, answered with the transaction
     * envelope refunds also use.
     *
     * @throws ConnectionException
     * @throws RequestException
     */
    public function create(): array
    {
        $response = $this->withApiKey()->post($this->getBaseUri().static::URI, $this->getParams());

        return $this->mappedReferenceKeys($this->parseTransactionResponse($response));
    }

    /**
     * Maps the reference data keys.
     *
     * Credit Card is a redirect flow: on success the response carries the
     * `redirectUrl` of the secure form the customer must be sent to, the
     * `transactionID` of that form and the `reference` the webhook later
     * echoes back (no entidade). The transaction the refund endpoint is keyed
     * by is a different id, and only arrives with the callback.
     */
    protected function mappedReferenceKeys(array $referenceData): array
    {
        return [
            'success' => ($referenceData['transactionStatus'] ?? null) === 'Success',
            'status' => $referenceData['transactionStatus'] ?? null,
            'identifier' => $this->id,
            'form_transaction_id' => $referenceData['transactionID'] ?? null,
            'reference' => $referenceData['reference'] ?? null,
            'url' => $referenceData['redirectUrl'] ?? null,
            'value' => $this->value,
        ];
    }

    /**
     * Returns the required params for making a request.
     */
    protected function getParams(): array
    {
        $payment = [
            'identifier' => $this->id,
            'amount' => [
                'value' => $this->value,
                'currency' => 'EUR',
            ],
            'successUrl' => $this->successUrl,
            'failUrl' => $this->failUrl,
            'backUrl' => $this->backUrl,
        ];

        if ($this->lang !== null) {
            $payment['lang'] = $this->lang;
        }

        return [
            'payment' => $payment,
            'customer' => [
                'email' => $this->email,
                'notify' => $this->notify,
            ],
        ];
    }
}
