<?php

namespace CodeTech\EuPago\Http\Requests;

use Illuminate\Validation\Rule;

class CreditCardCallbackRequest extends CallbackRequest
{
    /**
     * Get the validation rules that apply to the callback.
     */
    public function rules(): array
    {
        $rules = parent::rules();

        $rules['referencia'][] = Rule::exists('credit_card_references', 'reference');

        return $rules;
    }
}
