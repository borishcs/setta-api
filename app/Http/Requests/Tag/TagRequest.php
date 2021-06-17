<?php

namespace App\Http\Requests\Tag;

use Illuminate\Foundation\Http\FormRequest;

class TagRequest extends FormRequest
{
	public function authorize()
	{
		return true;
	}

	public function rules()
	{
		return [
			'title' => 'required|min:3|max:255',
		];
	}

	public function messages()
	{
		return [
			'title.required' => 'Título é obrigatório',
			'title.min' => 'Título deve ter ao menos 3 caracteres',
			'title.max' => 'Título deve ter no máximo 255 caracteres',
		];
	}
}
