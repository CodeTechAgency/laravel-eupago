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

        $query = MbwayReference::where('reference', $validatedData['referencia'])
            ->where('value', $validatedData['valor']);

        return $this->confirmPayment($query, $validatedData['transacao'], MBWayReferencePaid::class);
    }
}
