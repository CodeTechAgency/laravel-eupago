<?php

namespace CodeTech\EuPago\Http\Controllers;

use CodeTech\EuPago\Events\MBWayReferencePaid;
use CodeTech\EuPago\Http\Requests\MbWayCallbackRequest;
use CodeTech\EuPago\Models\MbwayReference;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MBWayController extends Controller
{
    /**
     * This endpoint is called when a MB Way reference is paid.
     *
     * @return JsonResponse
     */
    public function callback(Request $request)
    {
        $validatedData = $this->validateCallback($request, (new MbWayCallbackRequest)->rules());

        $reference = MbwayReference::where('reference', $validatedData['referencia'])
            ->where('value', $validatedData['valor'])
            ->where('state', 0)
            ->first();

        if (! $reference) {
            return response()->json(['response' => 'No pending reference found'])->setStatusCode(404);
        }

        $reference->update(['state' => 1]);

        // trigger event
        event(new MBWayReferencePaid($reference));

        return response()->json(['response' => 'Success'])->setStatusCode(200);
    }
}
