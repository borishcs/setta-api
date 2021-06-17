<?php

namespace App\Http\Requests\Paywall;

use Illuminate\Foundation\Http\FormRequest;

class SubscribeStoreRequest extends FormRequest
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
        return [
            'coupon' => 'string|min:2|max:255',
            'plan' => 'required|string|min:2|max:255',
        ];
    }
}
