<?php

namespace CodeTech\EuPago\Http\Controllers;

use CodeTech\EuPago\Events\MBReferencePaid;
use CodeTech\EuPago\Http\Requests\MbCallbackRequest;
use CodeTech\EuPago\Models\MbReference;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MBController extends Controller
{
    /**
     * This endpoint is called when a MB reference is paid.
     *
     * @return JsonResponse
     */
    public function callback(Request $request)
    {
        $validatedData = $this->validateCallback($request, (new MbCallbackRequest)->rules());

        $query = MbReference::where('reference', $validatedData['referencia'])
            ->accepting($validatedData['valor']);

        return $this->recordPayment($query, $validatedData['transacao'], $validatedData['valor']);
    }

    /**
     * Records the payment on the reference the query finds, marks it as paid,
     * then fires its event.
     *
     * The reference row is locked, and the checks below run under that lock,
     * so simultaneous deliveries are handled one at a time. The event fires after the commit, so a queued listener always
     * finds the payment stored. A redelivered notification is acknowledged
     * without recording or firing anything again.
     *
     * @param  Builder<MbReference>  $query
     */
    private function recordPayment(Builder $query, string $transaction, string $value): JsonResponse
    {
        $payment = $query->getConnection()->transaction(function () use ($query, $transaction, $value) {
            $reference = $query->lockForUpdate()->first();

            if (! $reference) {
                return null;
            }

            $recorded = $reference->payments()->where('transaction_id', $transaction)->first();

            if ($recorded) {
                return $recorded;
            }

            // A reference can allow repeat payments, so one already paid still
            // takes a new payment, as long as its payments are recorded to tell
            // it from a redelivered one.
            if ((int) $reference->getAttribute('state') !== 0 && ! $reference->payments()->exists()) {
                return null;
            }

            $reference->update([
                'state' => 1,
                'transaction_id' => $transaction,
            ]);

            return $reference->payments()
                ->create([
                    'transaction_id' => $transaction,
                    'value' => $value,
                ])
                ->setRelation('reference', $reference);
        });

        if (! $payment) {
            return $this->pendingReferenceNotFound();
        }

        if ($payment->wasRecentlyCreated) {
            event(new MBReferencePaid($payment->reference, $payment));
        }

        return $this->paymentConfirmed();
    }
}
