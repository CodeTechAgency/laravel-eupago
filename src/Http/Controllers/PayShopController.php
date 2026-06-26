<?php

namespace CodeTech\EuPago\Http\Controllers;

use CodeTech\EuPago\Events\PayShopReferencePaid;
use CodeTech\EuPago\Http\Requests\PayShopCallbackRequest;
use CodeTech\EuPago\Models\PayShopReference;

class PayShopController extends Controller
{
    /**
     * This endpoint is called when a PayShop reference is paid.
     *
     * @param PayShopCallbackRequest $request
     * @return \Illuminate\Http\JsonResponse|object
     */
    public function callback(PayShopCallbackRequest $request)
    {
        $validatedData = $request->validated();

        $reference = PayShopReference::where('reference', $validatedData['referencia'])
            ->where('value', $validatedData['valor'])
            ->where('state', 0)
            ->first();

        if (!$reference) {
            return response()->json(['response' => 'No pending reference found'])->setStatusCode(404);
        }

        $reference->update(['state' => 1]);

        // trigger event
        event(new PayShopReferencePaid($reference));

        return response()->json(['response' => 'Success'])->setStatusCode(200);
    }
}
