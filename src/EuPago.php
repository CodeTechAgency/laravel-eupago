<?php

namespace CodeTech\EuPago;

use CodeTech\EuPago\Auth\TokenProvider;
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
     * The refund endpoint of the management API, keyed by transaction id.
     */
    const REFUND_URI = '/api/management/v1.02/refund/';

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
     * Refunds a transaction, partially or in full. The transaction id is the
     * `transacao` value delivered by the payment callback.
     *
     * Rejections (e.g. a refund larger than the payment) come back as client
     * errors with a structured body, so they land in the error bag instead of
     * throwing — only transport and server errors throw.
     *
     * @throws ConnectionException
     * @throws RequestException
     */
    public function refund(int|string $transactionId, float $amount, ?string $reason = null, ?string $iban = null, ?string $bic = null): array
    {
        $params = array_filter([
            'amount' => $amount,
            'reason' => $reason,
            'iban' => $iban,
            'bic' => $bic,
        ], fn ($value) => $value !== null);

        $response = Http::withToken((new TokenProvider)->token())
            ->post($this->getBaseUri().static::REFUND_URI.$transactionId, $params)
            ->throwIfServerError();

        $refundData = $response->json();

        if (! is_array($refundData)) {
            $refundData = [];
        }

        if (($refundData['transactionStatus'] ?? null) !== 'Success') {
            $this->addError($refundData['code'] ?? null, $refundData['text'] ?? null);
        }

        return $this->mappedRefundKeys($refundData);
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
     * Maps the raw refund response to normalized keys.
     */
    protected function mappedRefundKeys(array $refundData): array
    {
        return [
            'success' => ($refundData['transactionStatus'] ?? null) === 'Success',
            'status' => $refundData['transactionStatus'] ?? null,
            'refund_id' => $refundData['refundId'] ?? null,
            'code' => $refundData['code'] ?? null,
            'text' => $refundData['text'] ?? null,
        ];
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
