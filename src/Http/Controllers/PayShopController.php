<?php

namespace CodeTech\EuPago\Http\Controllers;

use CodeTech\EuPago\Events\PayShopReferencePaid;
use CodeTech\EuPago\Http\Requests\PayShopCallbackRequest;
use CodeTech\EuPago\Models\PayShopReference;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PayShopController extends Controller
{
    /**
     * This endpoint is called when a PayShop reference is paid.
     *
     * @return JsonResponse
     */
    public function callback(Request $request)
    {
        $validatedData = $this->validateCallback($request, (new PayShopCallbackRequest)->rules());

        $reference = PayShopReference::where('reference', $validatedData['referencia'])
            ->where('value', $validatedData['valor'])
            ->where('state', 0)
            ->first();

        if (! $reference) {
            return response()->json(['response' => 'No pending reference found'])->setStatusCode(404);
        }

        $reference->update(['state' => 1]);

        // trigger event
        event(new PayShopReferencePaid($reference));

        return response()->json(['response' => 'Success'])->setStatusCode(200);
    }
}
