<?php

namespace CodeTech\EuPago\Http\Controllers;

use CodeTech\EuPago\Events\CreditCardReferencePaid;
use CodeTech\EuPago\Http\Requests\CreditCardCallbackRequest;
use CodeTech\EuPago\Models\CreditCardReference;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CreditCardController extends Controller
{
    /**
     * This endpoint is called when a Credit Card payment is confirmed.
     *
     * @return JsonResponse
     */
    public function callback(Request $request)
    {
        $validatedData = $this->validateCallback($request, (new CreditCardCallbackRequest)->rules());

        // Credit Card references are short per-channel counters, so unlike the
        // other methods `reference` is not unique on its own. The identifier
        // the callback echoes back pins the match to the right payment.
        $reference = CreditCardReference::where('reference', $validatedData['referencia'])
            ->where('identifier', $validatedData['identificador'])
            ->where('value', $validatedData['valor'])
            ->where('state', 0)
            ->first();

        if (! $reference) {
            return response()->json(['response' => 'No pending reference found'])->setStatusCode(404);
        }

        $reference->update([
            'state' => 1,
            'transaction_id' => $validatedData['transacao'],
        ]);

        // trigger event
        event(new CreditCardReferencePaid($reference));

        return response()->json(['response' => 'Success'])->setStatusCode(200);
    }
}
