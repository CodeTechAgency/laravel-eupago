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

        $query = PayShopReference::where('reference', $validatedData['referencia'])
            ->where('value', $validatedData['valor']);

        return $this->confirmPayment($query, $validatedData['transacao'], PayShopReferencePaid::class);
    }
}
