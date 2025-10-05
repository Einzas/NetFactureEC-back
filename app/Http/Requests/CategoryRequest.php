<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CategoryRequest extends FormRequest
{
    public function authorize()
    {
        // Ajusta según policies/roles si lo deseas
        return true;
    }

    public function rules()
    {
        $categoryId = null;
        // $this->route('category') puede ser modelo o id según binding
        if ($this->route('category')) {
            $routeValue = $this->route('category');
            $categoryId = is_object($routeValue) ? $routeValue->id : $routeValue;
        }

        return [
            'name' => [
                'required',
                'string',
                'max:100',
                Rule::unique('categories', 'name')->ignore($categoryId),
            ],
            'description' => 'nullable|string|max:255',
            'status' => 'required|in:active,inactive',
        ];
    }
}
