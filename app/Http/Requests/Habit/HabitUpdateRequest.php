<?php

namespace App\Http\Requests\Habit;

use Illuminate\Foundation\Http\FormRequest;

class HabitUpdateRequest extends FormRequest
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
            'repeat'            => 'nullable|array',
            'period'            => 'nullable|string',
            'title'             => 'nullable|string',
            'note'              => 'nullable|string',
            'final_date'        => 'nullable|date_format:Y-m-d H:i:s',
            'last_completed'    => 'nullable|date_format:Y-m-d H:i:s',
        ];
    }

    public function messages()
    {
        return [
            'title.min' => 'Título deve ter ao menos 3 caracteres',
            'title.max' => 'Título deve ter no máximo 255 caracteres',
            'note.max'  => 'Limite para o texto é de 500 caracteres',
        ];
    }
}
