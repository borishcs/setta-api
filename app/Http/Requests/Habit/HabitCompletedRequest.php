<?php

namespace App\Http\Requests\Habit;

use Illuminate\Foundation\Http\FormRequest;

class HabitCompletedRequest extends FormRequest
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
            'completed_at' => 'date_format:Y-m-d H:i:s',
        ];
    }

    public function messages()
    {
        return [
            //'completed_at.required' => 'completed_at é obrigatório',
            'completed_at.date_format' =>
                'Formato de data não é válido (Y-m-d H:i:s)',
        ];
    }
}
