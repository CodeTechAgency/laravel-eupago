<?php

namespace CodeTech\EuPago;

use Illuminate\Support\Facades\Http;

abstract class EuPago
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

        $referenceData = $response->json() ?? [];

        if (!($referenceData['sucesso'] ?? false)) {
            $this->addError($referenceData['estado'] ?? null, $referenceData['resposta'] ?? null);
        }

        return $this->mappedReferenceKeys($referenceData);
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
        $this->errors[$code] = html_entity_decode((string) $message);
    }

    /**
     * Returns the params required for the create request.
     *
     * @return array
     */
    abstract protected function getParams(): array;

    /**
     * Maps the raw EuPago response to normalized keys.
     *
     * @param array $referenceData
     * @return array
     */
    abstract protected function mappedReferenceKeys(array $referenceData): array;
}
