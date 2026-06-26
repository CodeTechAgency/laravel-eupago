<?php

namespace CodeTech\EuPago\MBWay;

use CodeTech\EuPago\EuPago;

class MBWay extends EuPago
{
    /**
     * The unique resource identifier.
     */
    const URI = '/clientes/rest_api/mbway/create';

    /**
     * The payment value.
     *
     * @var float
     */
    protected $value;

    /**
     * External identifier. Ex: the order id.
     *
     * @var int
     */
    protected $id;

    /**
     * The client's MB Way phone number.
     *
     * @var string
     */
    protected $alias;

    /**
     * Additional info.
     *
     * @var string
     */
    protected $description;

    /**
     * MBWay constructor.
     *
     * @param float $value
     * @param int $id
     * @param string $alias
     * @param string|null $description
     */
    public function __construct(float $value, int $id, string $alias, ?string $description = null)
    {
        $this->value       = $value;
        $this->id          = $id;
        $this->alias       = $alias;
        $this->description = $description;
    }

    /**
     * Maps the reference data keys.
     *
     * @param array $referenceData
     * @return array
     */
    protected function mappedReferenceKeys(array $referenceData): array
    {
        return [
            'success' => $referenceData['sucesso'] ?? null,
            'state' => $referenceData['estado'] ?? null,
            'response' => $referenceData['resposta'] ?? null,
            'reference' => $referenceData['referencia'] ?? null,
            'value' => $referenceData['valor'] ?? null,
            'alias' => $referenceData['alias'] ?? null,
        ];
    }

    /**
     * Returns the required params for making a request.
     *
     * @return array
     */
    protected function getParams(): array
    {
        return [
            'chave' => config('eupago.api_key'),
            'valor' => $this->value,
            'id' => $this->id,
            'alias' => $this->alias,
            'descricao' => $this->description,
        ];
    }
}
