<?php

namespace CodeTech\EuPago\Http\Requests;

use Illuminate\Validation\Rule;

class MbCallbackRequest extends CallbackRequest
{
    /**
     * Get the validation rules that apply to the callback.
     */
    public function rules(): array
    {
        $rules = parent::rules();

        $rules['referencia'][] = Rule::exists('mb_references', 'reference');

        return $rules;
    }
}
