<?php

namespace EuPago\MBWay;

use EuPago\EuPago;
use GuzzleHttp\Client;

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
     * The errors stored during the operations.
     *
     * @var array
     */
    protected $errors = [];

    /**
     * MBWay constructor.
     *
     * @param float $value
     * @param string $id
     * @param int $alias
     * @param string|null $description
     */
    public function __construct(float $value, int $id, string $alias, string $description = null)
    {
        $this->value       = $value;
        $this->id          = $id;
        $this->alias       = $alias;
        $this->description = $description;
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
     * Generates a new MBWay reference.
     *
     * @return mixed
     */
    public function create()
    {
        $client = new Client(['base_uri' => $this->getBaseUri()]);

        $response = $client->post(self::URI, $this->getParams());

        $referenceData = json_decode($response->getBody()->getContents(), true);

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
            'form_params' => [
                'chave' => config('eupago.api_key'),
                'valor' => $this->value,
                'id' => $this->id,
                'alias' => $this->alias,
                'descricao' => $this->description,
            ]
        ];
    }
}
