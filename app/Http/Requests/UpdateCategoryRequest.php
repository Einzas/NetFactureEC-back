<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    // public function rules(): array
    // {
    //     $id = $this->route('category'); // ID actual para excluir de unique

    //     return [
    //         'name' => 'required|string|max:100|unique:categories,name,' . $id,
    //         'description' => 'nullable|string|max:255',
    //         'status' => 'required|in:active,inactive',
    //     ];
    // }

    public function rules()
{
    return [
        'name' => 'required|string|max:255|unique:categories,name,' . $this->route('category')->id,
        'description' => 'nullable|string|max:500',
        'status' => 'required|in:active,inactive',
    ];
}

    public function messages(): array
    {
        return [
            'name.required' => 'El nombre es obligatorio.',
            'name.unique' => 'Ya existe una categoría con ese nombre.',
            'status.required' => 'El estado es obligatorio.',
            'status.in' => 'El estado debe ser activo o inactivo.',
        ];
    }
}
