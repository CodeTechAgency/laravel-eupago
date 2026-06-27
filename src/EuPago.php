<?php

namespace CodeTech\EuPago;

use Illuminate\Support\Facades\Http;

class EuPago
{
    /**
     * The test endpoint
     */
    const TEST_ENDPOINT = 'https://sandbox.eupago.pt';

    /**
     * The production endpoint
     */
    const PROD_ENDPOINT = 'https://clientes.eupago.pt';

    /**
     * The reference status (info) endpoint, shared across reference types.
     */
    const STATUS_URI = '/clientes/rest_api/multibanco/info';

    /**
     * The errors stored during the operations.
     *
     * @var array
     */
    protected $errors = [];

    /**
     * Returns the base uri, based on the current environment.
     *
     * @return string
     */
    public function getBaseUri()
    {
        return config('eupago.env') == 'prod' ? self::PROD_ENDPOINT : self::TEST_ENDPOINT;
    }

    /**
     * Generates a new reference.
     *
     * @return array
     * @throws \Illuminate\Http\Client\ConnectionException
     * @throws \Illuminate\Http\Client\RequestException
     */
    public function create(): array
    {
        $response = Http::asForm()->post($this->getBaseUri() . static::URI, $this->getParams())->throw();

        $referenceData = $response->json();

        if (!is_array($referenceData)) {
            $referenceData = [];
        }

        if (!($referenceData['sucesso'] ?? false)) {
            $this->addError($referenceData['estado'] ?? null, $referenceData['resposta'] ?? null);
        }

        return $this->mappedReferenceKeys($referenceData);
    }

    /**
     * Queries the current status of an existing reference.
     *
     * @param string $reference
     * @param string|null $entity
     * @return array
     * @throws \Illuminate\Http\Client\ConnectionException
     * @throws \Illuminate\Http\Client\RequestException
     */
    public function status(string $reference, ?string $entity = null): array
    {
        $params = [
            'chave' => config('eupago.api_key'),
            'referencia' => $reference,
        ];

        if ($entity !== null) {
            $params['entidade'] = $entity;
        }

        $response = Http::asForm()->post($this->getBaseUri() . self::STATUS_URI, $params)->throw();

        $statusData = $response->json();

        if (!is_array($statusData)) {
            $statusData = [];
        }

        if (!($statusData['sucesso'] ?? false)) {
            $this->addError($statusData['estado'] ?? null, $statusData['resposta'] ?? null);
        }

        return $this->mappedStatusKeys($statusData);
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
     * Determines whether any errors were stored.
     *
     * @return bool
     */
    public function hasErrors(): bool
    {
        return count($this->errors) > 0;
    }

    /**
     * Adds an error to the bag.
     *
     * @param $code
     * @param $message
     */
    protected function addError($code, $message)
    {
        $this->errors[$code ?? 'unknown'] = html_entity_decode((string) $message);
    }

    /**
     * Returns the params required for the create request. Payment methods override this.
     *
     * @return array
     */
    protected function getParams(): array
    {
        throw new \BadMethodCallException(static::class . ' must implement getParams().');
    }

    /**
     * Maps the raw EuPago response to normalized keys. Payment methods override this.
     *
     * @param array $referenceData
     * @return array
     */
    protected function mappedReferenceKeys(array $referenceData): array
    {
        throw new \BadMethodCallException(static::class . ' must implement mappedReferenceKeys().');
    }

    /**
     * Maps the raw reference-status response to normalized keys.
     *
     * @param array $statusData
     * @return array
     */
    protected function mappedStatusKeys(array $statusData): array
    {
        return [
            'success' => $statusData['sucesso'] ?? null,
            'state' => $statusData['estado'] ?? null,
            'response' => $statusData['resposta'] ?? null,
            'entity' => $statusData['entidade'] ?? null,
            'reference' => $statusData['referencia'] ?? null,
            'identifier' => $statusData['identificador'] ?? null,
            'reference_state' => $statusData['estado_referencia'] ?? null,
            'created_date' => $statusData['data_criacao'] ?? null,
            'created_time' => $statusData['hora_criacao'] ?? null,
            'archived' => $statusData['arquivada'] ?? null,
        ];
    }
}
