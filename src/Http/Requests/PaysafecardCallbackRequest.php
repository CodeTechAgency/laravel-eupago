<?php

namespace CodeTech\EuPago\Http\Requests;

use Illuminate\Validation\Rule;

class PaysafecardCallbackRequest extends CallbackRequest
{
    /**
     * Get the validation rules that apply to the callback.
     *
     * Paysafecard has no static referencia of our own to match on, so the
     * pending reference is keyed by `identificador` (the external id we sent
     * at create time, echoed back by the webhook).
     */
    public function rules(): array
    {
        $rules = parent::rules();

        $rules['identificador'] = ['required', Rule::exists('paysafecard_references', 'identifier')];

        return $rules;
    }
}
