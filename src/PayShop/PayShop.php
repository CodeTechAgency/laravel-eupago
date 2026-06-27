<?php

namespace CodeTech\EuPago\PayShop;

use CodeTech\EuPago\EuPago;

class PayShop extends EuPago
{
    /**
     * The unique resource identifier.
     */
    const URI = '/clientes/rest_api/payshop/create';

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
     * PayShop constructor.
     */
    public function __construct(float $value, string $id)
    {
        $this->value = $value;
        $this->id = $id;
    }

    /**
     * Maps the reference data keys.
     */
    protected function mappedReferenceKeys(array $referenceData): array
    {
        return [
            'success' => $referenceData['sucesso'] ?? null,
            'state' => $referenceData['estado'] ?? null,
            'response' => $referenceData['resposta'] ?? null,
            'reference' => $referenceData['referencia'] ?? null,
            'value' => $referenceData['valor'] ?? null,
        ];
    }

    /**
     * Returns the required params for making a request.
     */
    protected function getParams(): array
    {
        return [
            'chave' => config('eupago.api_key'),
            'valor' => $this->value,
            'id' => $this->id,
        ];
    }
}
