<?php

namespace CodeTech\EuPago\Http\Controllers;

use CodeTech\EuPago\Http\Requests\CallbackRequest;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class Controller extends BaseController
{
    /**
     * Logs and validates an incoming EuPago callback, returning the validated data.
     *
     * The caller is checked on its own first, so a request without the
     * channel and API key never reaches the remaining rules — some of which
     * query the database and would otherwise reveal which references exist.
     */
    protected function validateCallback(Request $request, array $rules): array
    {
        $payload = $request->all();

        if (array_key_exists('chave_api', $payload)) {
            $payload['chave_api'] = '***';
        }

        // Log the path only (the query string also carries chave_api) with the key masked.
        Log::info('EuPago Callback', [
            'url' => $request->url(),
            'payload' => $payload,
        ]);

        $this->validateOrFail($request, CallbackRequest::callerRules());

        return $this->validateOrFail($request, $rules);
    }

    /**
     * Marks the pending reference the query finds as paid, then fires the
     * given event with it.
     *
     * The reference row is locked, and a reference already paid by this
     * transaction is found as well, so of simultaneous deliveries only one
     * marks it as paid and the others are acknowledged without firing the
     * event again. The event fires after the commit, so a queued listener
     * always finds the payment stored.
     *
     * @param  class-string  $event
     */
    protected function confirmPayment(Builder $query, string $transaction, string $event): JsonResponse
    {
        $reference = $query->getConnection()->transaction(function () use ($query, $transaction) {
            // A reference already paid by this transaction comes first, so a
            // redelivery never marks another pending match as paid.
            $reference = $query
                ->where(fn ($query) => $query->where('state', 0)->orWhere('transaction_id', $transaction))
                ->orderByDesc('state')
                ->lockForUpdate()
                ->first();

            if ($reference && $reference->getAttribute('transaction_id') !== $transaction) {
                $reference->update([
                    'state' => 1,
                    'transaction_id' => $transaction,
                ]);
            }

            return $reference;
        });

        if (! $reference) {
            return $this->pendingReferenceNotFound();
        }

        if ($reference->wasChanged('state')) {
            event(new $event($reference));
        }

        return $this->paymentConfirmed();
    }

    /**
     * The response for a payment that was confirmed.
     */
    protected function paymentConfirmed(): JsonResponse
    {
        return response()->json(['response' => 'Success']);
    }

    /**
     * The response for a payment that matches no pending reference.
     */
    protected function pendingReferenceNotFound(): JsonResponse
    {
        return response()->json(['response' => 'No pending reference found'], 404);
    }

    /**
     * Validates the request against the rules, or aborts with a 422.
     */
    private function validateOrFail(Request $request, array $rules): array
    {
        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            throw new HttpResponseException(response()->json($validator->errors(), 422));
        }

        return $validator->validated();
    }
}
