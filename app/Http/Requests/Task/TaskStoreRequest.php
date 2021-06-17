<?php

namespace App\Http\Requests\Task;

use Illuminate\Foundation\Http\FormRequest;

class TaskStoreRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'parent_id' => 'nullable|string',
            'tag_id' => 'nullable|string',
            'habit_id' => 'nullable|string',
            'title' => 'required|string|min:3|max:255',
            'period' => 'nullable|string',
            'note' => 'nullable|string|max:500',
            'when' => 'nullable|string|max:500',
            'order' => 'nullable|integer',
            'completed_at' => 'nullable|date_format:Y-m-d H:i:s',
            'subtasks' => 'array',
        ];
    }

    public function messages()
    {
        return [
            'title.required' => 'Título é obrigatório',
            'title.min' => 'Título deve ter ao menos 3 caracteres',
            'title.max' => 'Título deve ter no máximo 255 caracteres',
            'note.max' => 'Limite para o texto é de 500 caracteres',
            'when' => 'formato incorreto',
        ];
    }
}
