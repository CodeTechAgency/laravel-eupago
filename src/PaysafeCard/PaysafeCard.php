<?php

namespace CodeTech\EuPago\PaysafeCard;

use CodeTech\EuPago\EuPago;

class PaysafeCard extends EuPago
{
    /**
     * The unique resource identifier.
     */
    const URI = '/clientes/rest_api/paysafecard/create';

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
     * The URL the customer is redirected to after completing the payment.
     *
     * @var string|null
     */
    protected $returnUrl;

    /**
     * PaysafeCard constructor.
     */
    public function __construct(float $value, string $id, ?string $returnUrl = null)
    {
        $this->value = $value;
        $this->id = $id;
        $this->returnUrl = $returnUrl;
    }

    /**
     * Maps the reference data keys.
     *
     * Unlike the reference-based methods (MB/MBWay/PayShop), PaysafeCard is a
     * redirect flow: on success the response carries the payment `url` the
     * customer must be redirected to, plus a `referencia` the webhook later
     * echoes back (no entidade).
     *
     * NOTE: the success-response shape (`url` + `referencia` keys) matches
     * EuPago's official WooCommerce plugin, which reads exactly these fields
     * from this endpoint; it could not be verified live because the sandbox
     * PaysafeCard link is down (estado -11).
     */
    protected function mappedReferenceKeys(array $referenceData): array
    {
        return [
            'success' => $referenceData['sucesso'] ?? null,
            'state' => $referenceData['estado'] ?? null,
            'response' => $referenceData['resposta'] ?? null,
            'identifier' => $this->id,
            'reference' => $referenceData['referencia'] ?? null,
            'url' => $referenceData['url'] ?? null,
            'value' => $this->value,
        ];
    }

    /**
     * Returns the required params for making a request.
     */
    protected function getParams(): array
    {
        $params = [
            'chave' => config('eupago.api_key'),
            'valor' => $this->value,
            'id' => $this->id,
        ];

        if ($this->returnUrl !== null) {
            $params['url_retorno'] = $this->returnUrl;
        }

        return $params;
    }
}
