<?php

namespace CodeTech\EuPago\Http\Controllers;

use CodeTech\EuPago\Events\PaysafeCardReferencePaid;
use CodeTech\EuPago\Http\Requests\PaysafeCardCallbackRequest;
use CodeTech\EuPago\Models\PaysafeCardReference;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PaysafeCardController extends Controller
{
    /**
     * This endpoint is called when a PaysafeCard payment is confirmed.
     *
     * @return JsonResponse
     */
    public function callback(Request $request)
    {
        $validatedData = $this->validateCallback($request, (new PaysafeCardCallbackRequest)->rules());

        $reference = PaysafeCardReference::where('reference', $validatedData['referencia'])
            ->where('value', $validatedData['valor'])
            ->where('state', 0)
            ->first();

        if (! $reference) {
            return response()->json(['response' => 'No pending reference found'])->setStatusCode(404);
        }

        $reference->update(['state' => 1]);

        // trigger event
        event(new PaysafeCardReferencePaid($reference));

        return response()->json(['response' => 'Success'])->setStatusCode(200);
    }
}
