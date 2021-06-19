<?php

namespace CodeTech\EuPago\Http\Controllers;

use Barryvdh\Debugbar\Controllers\BaseController;
use EuPago\Events\MBWayReferencePaid;
use EuPago\Http\Requests\MbWayCallbackRequest;
use EuPago\Models\MbwayReference;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Support\Facades\Log;

class MBWayController extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    /**
     * This endpoint is called when a MB Way reference is paid.
     *
     * @param MbWayCallbackRequest $request
     * @return \Illuminate\Http\JsonResponse|object
     */
    public function callback(MbWayCallbackRequest $request)
    {
        $validatedData = $request->validated();

        $reference = MbwayReference::where('reference', $validatedData['referencia'])
            ->where('value', $validatedData['valor'])
            ->where('state', 0)
            ->first();

        if (!$reference) {
            return response()->json(['response' => 'No pending reference found'])->setStatusCode(404);
        }

        $reference->update(['state' => 1]);

        // trigger event
        event(new MBWayReferencePaid($reference));

        return response()->json(['response' => 'Success'])->setStatusCode(200);
    }
}
