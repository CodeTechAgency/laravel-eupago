<?php

namespace CodeTech\EuPago\Http\Controllers;

use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class Controller extends BaseController
{
    /**
     * Logs and validates an incoming EuPago callback, returning the validated data.
     */
    protected function validateCallback(Request $request, array $rules): array
    {
        $payload = $request->all();

        if (array_key_exists('chave_api', $payload)) {
            $payload['chave_api'] = '***';
        }

        // Log the path only (the query string also carries chave_api) with the key masked.
        Log::info('EuPago Callback', [
            'url' => $request->url(),
            'payload' => $payload,
        ]);

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            throw new HttpResponseException(response()->json($validator->errors(), 422));
        }

        return $validator->validated();
    }
}
