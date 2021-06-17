<?php

namespace App\Http\Requests\Timer;

use Illuminate\Foundation\Http\FormRequest;

class TimerAddRequest extends FormRequest
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
            'add' => 'nullable|date_format:H:i:s',
            'type' => 'required|integer',
        ];
    }
}
