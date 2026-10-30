<?php

namespace CodeTech\EuPago\Http\Requests;

use CodeTech\EuPago\Exceptions\MissingConfiguration;
use Illuminate\Validation\Rule;

abstract class CallbackRequest
{
    /**
     * Get the validation rules that identify the caller as Eupago.
     *
     * A blank channel or API key would reject every callback as if it were
     * forged, so it is reported as the configuration error it is instead.
     *
     * @throws MissingConfiguration
     */
    public static function callerRules(): array
    {
        foreach (['channel' => 'EUPAGO_CHANNEL', 'api_key' => 'EUPAGO_API_KEY'] as $key => $variable) {
            if (blank(config("eupago.{$key}"))) {
                throw MissingConfiguration::forSetting($key, $variable);
            }
        }

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
