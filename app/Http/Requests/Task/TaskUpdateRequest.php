<?php

namespace App\Http\Requests\Task;

use Illuminate\Foundation\Http\FormRequest;

class TaskUpdateRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'parent_id'     => 'nullable|integer',
            'tag_id'        => 'nullable|integer',
            'habit_id'      => 'nullable|integer',
            'title'         => 'nullable|string|min:3|max:255',
            'period'        => 'nullable|integer',
            'note'          => 'nullable|string|max:500',
            'due_date'      => 'date_format:Y-m-d H:i:s',
            'order'         => 'nullable|integer',
            'completed_at'  => 'nullable|date_format:Y-m-d H:i:s',
            'subtasks'      => 'array',
        ];
    }

    public function messages()
    {
        return [
            'title.min'     => 'Título deve ter ao menos 3 caracteres',
            'title.max'     => 'Título deve ter no máximo 255 caracteres',
            'note.max'      => 'Limite para o texto é de 500 caracteres',
            'due_date'      => 'Data com formato incorreto',
            'completed_at'  => 'date_format:Y-m-d H:i:s',
        ];
    }
}
