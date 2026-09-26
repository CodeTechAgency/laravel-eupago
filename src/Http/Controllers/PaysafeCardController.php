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

        $query = PaysafeCardReference::where('reference', $validatedData['referencia'])
            ->where('value', $validatedData['valor']);

        return $this->confirmPayment($query, $validatedData['transacao'], PaysafeCardReferencePaid::class);
    }
}
