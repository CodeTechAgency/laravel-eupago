<?php

namespace CodeTech\EuPago\Http\Controllers;

use CodeTech\EuPago\Events\PaysafecardReferencePaid;
use CodeTech\EuPago\Http\Requests\PaysafecardCallbackRequest;
use CodeTech\EuPago\Models\PaysafecardReference;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PaysafecardController extends Controller
{
    /**
     * This endpoint is called when a Paysafecard payment is confirmed.
     *
     * @return JsonResponse
     */
    public function callback(Request $request)
    {
        $validatedData = $this->validateCallback($request, (new PaysafecardCallbackRequest)->rules());

        $reference = PaysafecardReference::where('identifier', $validatedData['identificador'])
            ->where('value', $validatedData['valor'])
            ->where('state', 0)
            ->first();

        if (! $reference) {
            return response()->json(['response' => 'No pending reference found'])->setStatusCode(404);
        }

        $reference->update(['state' => 1]);

        // trigger event
        event(new PaysafecardReferencePaid($reference));

        return response()->json(['response' => 'Success'])->setStatusCode(200);
    }
}
