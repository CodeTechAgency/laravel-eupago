<?php

namespace CodeTech\EuPago\Paysafecard;

use CodeTech\EuPago\EuPago;

class Paysafecard extends EuPago
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
     * Paysafecard constructor.
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
     * Unlike the reference-based methods (MB/MBWay/PayShop), Paysafecard is a
     * redirect flow: the create response carries no entidade/referencia. The
     * payment URL the customer must be sent to comes back in `resposta` on
     * success, and the external `id` we sent is what the webhook later echoes
     * back as `identificador` — so we persist our own id/value as the match key.
     *
     * NOTE: the success-response shape is documented only as sucesso/estado/
     * resposta and could not be verified live (sandbox returned estado -11).
     * The `url` mapping below assumes `resposta` holds the redirect URL on
     * success; reconcile against a real response before relying on it.
     */
    protected function mappedReferenceKeys(array $referenceData): array
    {
        return [
            'success' => $referenceData['sucesso'] ?? null,
            'state' => $referenceData['estado'] ?? null,
            'identifier' => $this->id,
            'url' => $referenceData['resposta'] ?? null,
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
