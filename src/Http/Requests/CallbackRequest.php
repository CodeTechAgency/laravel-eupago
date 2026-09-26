<?php

namespace CodeTech\EuPago\Http\Requests;

use Illuminate\Validation\Rule;

abstract class CallbackRequest
{
    /**
     * Get the validation rules that identify the caller as Eupago.
     */
    public static function callerRules(): array
    {
        return [
            'canal' => [
                'required',
                Rule::in([config('eupago.channel')]),
            ],
            'chave_api' => [
                'required',
                Rule::in([config('eupago.api_key')]),
            ],
        ];
    }

    /**
     * Get the validation rules that apply to the callback.
     */
    public function rules(): array
    {
        return array_merge(static::callerRules(), [
            'valor' => 'required|numeric',
            'referencia' => ['required'],
            'transacao' => 'required',
            'identificador' => 'required',
            'mp' => 'required',
            'data' => 'required|date_format:Y-m-d:H:i:s',
            'entidade' => 'required',
            'comissao' => 'required',
            'local' => 'nullable',
        ]);
    }
}
