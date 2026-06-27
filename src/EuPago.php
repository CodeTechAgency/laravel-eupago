<?php

namespace CodeTech\EuPago;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
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
     * The create endpoint for the payment method. Each payment method overrides this.
     */
    const URI = '';

    /**
     * The reference-info endpoint used by status().
     *
     * EuPago exposes a single reference-info endpoint, keyed by entidade +
     * referencia, that resolves any reference type — MB, MB Way and PayShop
     * references all query through it (verified against the live API). Despite
     * the "multibanco" path segment, it is not Multibanco-specific. Subclasses
     * may override this constant should a method ever require a dedicated
     * endpoint.
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
     * @throws ConnectionException
     * @throws RequestException
     */
    public function create(): array
    {
        $response = Http::asForm()->post($this->getBaseUri().static::URI, $this->getParams())->throw();

        $referenceData = $response->json();

        if (! is_array($referenceData)) {
            $referenceData = [];
        }

        if (! ($referenceData['sucesso'] ?? false)) {
            $this->addError($referenceData['estado'] ?? null, $referenceData['resposta'] ?? null);
        }

        return $this->mappedReferenceKeys($referenceData);
    }

    /**
     * Queries the current status of an existing reference.
     *
     * @throws ConnectionException
     * @throws RequestException
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

        $response = Http::asForm()->post($this->getBaseUri().static::STATUS_URI, $params)->throw();

        $statusData = $response->json();

        if (! is_array($statusData)) {
            $statusData = [];
        }

        if (! ($statusData['sucesso'] ?? false)) {
            $this->addError($statusData['estado'] ?? null, $statusData['resposta'] ?? null);
        }

        return $this->mappedStatusKeys($statusData);
    }

    /**
     * Returns the errors.
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    /**
     * Determines whether any errors were stored.
     */
    public function hasErrors(): bool
    {
        return count($this->errors) > 0;
    }

    /**
     * Adds an error to the bag.
     */
    protected function addError($code, $message)
    {
        $this->errors[$code ?? 'unknown'] = html_entity_decode((string) $message);
    }

    /**
     * Returns the params required for the create request. Payment methods override this.
     */
    protected function getParams(): array
    {
        throw new \BadMethodCallException(static::class.' must implement getParams().');
    }

    /**
     * Maps the raw EuPago response to normalized keys. Payment methods override this.
     */
    protected function mappedReferenceKeys(array $referenceData): array
    {
        throw new \BadMethodCallException(static::class.' must implement mappedReferenceKeys().');
    }

    /**
     * Maps the raw reference-status response to normalized keys.
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
