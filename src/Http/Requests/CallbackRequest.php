<?php

namespace CodeTech\EuPago\Http\Requests;

use Illuminate\Validation\Rule;

abstract class CallbackRequest
{
    /**
     * Get the validation rules that apply to the callback.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'valor' => 'required',
            'canal' => [
                'required',
                Rule::in([config('eupago.channel')])
            ],
            'referencia' => ['required'],
            'transacao' => 'required',
            'identificador' => 'required',
            'mp' => 'required',
            'chave_api' => [
                'required',
                Rule::in([config('eupago.api_key')])
            ],
            'data' => 'required|date_format:Y-m-d:H:i:s',
            'entidade' => 'required',
            'comissao' => 'required',
            'local' => 'nullable',
        ];
    }
}
