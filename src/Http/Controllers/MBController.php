<?php

namespace CodeTech\EuPago\Http\Controllers;

use CodeTech\EuPago\Events\MBReferencePaid;
use CodeTech\EuPago\Http\Requests\MbCallbackRequest;
use CodeTech\EuPago\Models\MbReference;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MBController extends Controller
{
    /**
     * This endpoint is called when a MB reference is paid.
     *
     * @return JsonResponse|object
     */
    public function callback(Request $request)
    {
        $validatedData = $this->validateCallback($request, (new MbCallbackRequest)->rules());

        $reference = MbReference::where('reference', $validatedData['referencia'])
            ->where('value', $validatedData['valor'])
            ->where('state', 0)
            ->first();

        if (! $reference) {
            return response()->json(['response' => 'No pending reference found'])->setStatusCode(404);
        }

        $reference->update(['state' => 1]);

        // trigger event
        event(new MBReferencePaid($reference));

        return response()->json(['response' => 'Success'])->setStatusCode(200);
    }
}
