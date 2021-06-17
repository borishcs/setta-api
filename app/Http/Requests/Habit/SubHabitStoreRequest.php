<?php

namespace App\Http\Requests\Habit;

use Illuminate\Foundation\Http\FormRequest;

class SubHabitStoreRequest extends FormRequest
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
            'parent_id' => 'required|string',
            'title' => 'required',
        ];
    }

    public function messages()
    {
        return [
            'title.required' => 'Titulo é obrigatório',
            'parent_id.integer' => 'Tipo de dado integer obrigatório',
            'parent_id.required' => 'parent_id é obrigatório',
        ];
    }
}
