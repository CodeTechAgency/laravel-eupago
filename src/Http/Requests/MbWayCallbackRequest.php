<?php

namespace CodeTech\EuPago\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class MbWayCallbackRequest extends FormRequest
{

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        Log::info(
            'EuPago MB Way Callback',
            [
                'url' => $this->fullUrl(),
                'payload' => $this->all()
            ]
        );

        return [
            'valor' => 'required',
            'canal' => [
                'required',
                Rule::in([config('eupago.channel')])
            ],
            'referencia' => 'required|exists:mbway_references,reference',
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

    /**
     * Failed validation disable redirect
     *
     * @param Validator $validator
     */
    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json($validator->errors(), 422));
    }

}
