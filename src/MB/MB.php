<?php

namespace CodeTech\EuPago\MB;

use Carbon\Carbon;
use CodeTech\EuPago\EuPago;
use Illuminate\Support\Facades\Http;

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
     * @var int
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
     * @var boolean
     */
    protected $allowDuplication;

    /**
     * The errors stored during the operations.
     *
     * @var array
     */
    protected $errors = [];

    /**
     * MB constructor.
     *
     * @param float $value
     * @param string $id
     * @param Carbon $startDate
     * @param Carbon $endDate
     * @param float $minValue
     * @param float $maxValue
     * @param bool $allowDuplication
     */
    public function __construct(float $value, string $id, Carbon $startDate, Carbon $endDate, float $minValue, float $maxValue, bool $allowDuplication = false)
    {
        $this->value            = $value;
        $this->id               = $id;
        $this->startDate        = $startDate->format('Y-m-d');
        $this->endDate          = $endDate->format('Y-m-d');
        $this->minValue         = $minValue;
        $this->maxValue         = $maxValue;
        $this->allowDuplication = $allowDuplication;
    }

    /**
     * Returns the errors.
     *
     * @return array
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    /**
     * Adds an error to the bag.
     *
     * @param $code
     * @param $message
     */
    protected function addError($code, $message)
    {
        $this->errors[$code] = html_entity_decode($message);
    }

    /**
     * Determines whether errors are logged.
     *
     * @return bool
     */
    public function hasErrors()
    {
        return count($this->errors) > 0;
    }

    /**
     * Generates a new MB reference.
     *
     * @return array
     * @throws \Illuminate\Http\Client\ConnectionException
     */
    public function create(): array
    {
        $response = Http::asForm()->post($this->getBaseUri() . self::URI, $this->getParams())->throw();

        $referenceData = $response->json();

        if (!$referenceData['sucesso']) {
            $this->addError($referenceData['estado'], $referenceData['resposta']);
        }

        return $this->mappedReferenceKeys($referenceData);
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
     *
     * @return array
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
