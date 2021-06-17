<?php

namespace App\Http\Requests\Habit;

use Illuminate\Foundation\Http\FormRequest;

class HabitStoreRequest extends FormRequest
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
            'task_id' => 'nullable|string',
            'habit_setta_id' => 'nullable|string',
            'note' => 'nullable|string|max:500',
            'final_date' => 'nullable|date_format:Y-m-d H:i:s',
            'title' => 'required|string|min:3|max:255',
            'period' => 'required|string',
            'repeat' => 'required|array',
        ];
    }

    public function messages()
    {
        return [
            'title.required' => 'Título é obrigatório',
            'title.min' => 'Título deve ter ao menos 3 caracteres',
            'title.max' => 'Título deve ter no máximo 255 caracteres',
            'note.max' => 'Limite para o texto é de 500 caracteres',
            'repeat.required' => 'Repeat é obrigatório',
            'period.required' => 'Periodo é obrigatório',
            'period.string' => 'Tipo de dado integer obrigatório',
        ];
    }
}
