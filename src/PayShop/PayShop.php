<?php

namespace CodeTech\EuPago\PayShop;

use CodeTech\EuPago\EuPago;
use Illuminate\Support\Facades\Http;

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
     * The errors stored during the operations.
     *
     * @var array
     */
    protected $errors = [];

    /**
     * PayShop constructor.
     *
     * @param float $value
     * @param string $id
     */
    public function __construct(float $value, string $id)
    {
        $this->value = $value;
        $this->id    = $id;
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
     * Generates a new PayShop reference.
     *
     * @return array
     * @throws \Illuminate\Http\Client\ConnectionException
     * @throws \Illuminate\Http\Client\RequestException
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
            'reference' => $referenceData['referencia'] ?? null,
            'value' => $referenceData['valor'] ?? null,
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
        ];
    }
}
