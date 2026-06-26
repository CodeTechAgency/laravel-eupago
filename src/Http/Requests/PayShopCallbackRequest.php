<?php

namespace CodeTech\EuPago\Http\Requests;

use Illuminate\Validation\Rule;

class PayShopCallbackRequest extends CallbackRequest
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
        $parentRules = parent::rules();

        $parentRules['referencia'][] = Rule::exists('payshop_references', 'reference');

        return $parentRules;
    }
}
