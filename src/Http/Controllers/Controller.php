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
     *
     * @param Request $request
     * @param array $rules
     * @return array
     */
    protected function validateCallback(Request $request, array $rules): array
    {
        Log::info('EuPago Callback', [
            'url' => $request->fullUrl(),
            'payload' => $request->all(),
        ]);

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            throw new HttpResponseException(response()->json($validator->errors(), 422));
        }

        return $validator->validated();
    }
}
