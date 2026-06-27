<?php

namespace CodeTech\EuPago\MB;

use Carbon\Carbon;
use CodeTech\EuPago\EuPago;

class MB extends EuPago
{
    /**
     * The unique resource identifier.
     */
    const URI = '/clientes/rest_api/multibanco/create';

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
     * The payment's start date limit.
     *
     * @var string
     */
    protected $startDate;

    /**
     * The payment's end date limit.
     *
     * @var string
     */
    protected $endDate;

    /**
     * The payment's min value.
     *
     * @var float
     */
    protected $minValue;

    /**
     * The payment's max value.
     *
     * @var float
     */
    protected $maxValue;

    /**
     * Indicates if duplicated payments are allowed.
     *
     * @var bool
     */
    protected $allowDuplication;

    /**
     * MB constructor.
     */
    public function __construct(float $value, string $id, Carbon $startDate, Carbon $endDate, float $minValue, float $maxValue, bool $allowDuplication = false)
    {
        $this->value = $value;
        $this->id = $id;
        $this->startDate = $startDate->format('Y-m-d');
        $this->endDate = $endDate->format('Y-m-d');
        $this->minValue = $minValue;
        $this->maxValue = $maxValue;
        $this->allowDuplication = $allowDuplication;
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
            'entity' => $referenceData['entidade'] ?? null,
            'reference' => $referenceData['referencia'] ?? null,
            'value' => $referenceData['valor'] ?? null,
            'min_value' => $referenceData['valor_minimo'] ?? null,
            'max_value' => $referenceData['valor_maximo'] ?? null,
            'start_date' => $referenceData['data_inicio'] ?? null,
            'end_date' => $referenceData['data_fim'] ?? null,
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
            'data_inicio' => $this->startDate,
            'data_fim' => $this->endDate,
            'valor_minimo' => $this->minValue,
            'valor_maximo' => $this->maxValue,
            'per_dup' => $this->allowDuplication,
        ];
    }
}
