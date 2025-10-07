<?php

namespace App\Http\Requests\File;

use Illuminate\Foundation\Http\FormRequest;

class UploadFileRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // La autorización se maneja con JWT middleware
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'file' => [
                'required',
                'file',
                'max:10240', // 10MB máximo
            ],
            'file_type' => [
                'required',
                'string',
                'in:p12,pfx,pdf,xml,xlsx,xls,csv,txt,jpg,jpeg,png,zip,rar',
            ],
            'description' => [
                'nullable',
                'string',
                'max:500',
            ],
            'expires_in_minutes' => [
                'nullable',
                'integer',
                'min:1',
                'max:1440', // Máximo 24 horas
            ],
            'is_session_file' => [
                'nullable',
                'boolean',
            ],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'file.required' => 'El archivo es obligatorio',
            'file.file' => 'Debe proporcionar un archivo válido',
            'file.max' => 'El archivo no debe superar los 10MB',
            'file_type.required' => 'El tipo de archivo es obligatorio',
            'file_type.in' => 'El tipo de archivo no es válido. Tipos permitidos: p12, pfx, pdf, xml, xlsx, xls, csv, txt, jpg, jpeg, png, zip, rar',
            'description.max' => 'La descripción no debe superar los 500 caracteres',
            'expires_in_minutes.integer' => 'El tiempo de expiración debe ser un número entero',
            'expires_in_minutes.min' => 'El tiempo de expiración debe ser al menos 1 minuto',
            'expires_in_minutes.max' => 'El tiempo de expiración no debe superar las 24 horas (1440 minutos)',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Si es un archivo P12/PFX y no se especificó tiempo de expiración, establecer expiración de sesión
        if (in_array($this->file_type, ['p12', 'pfx']) && !$this->expires_in_minutes) {
            $this->merge([
                'is_session_file' => true,
                'expires_in_minutes' => 120, // 2 horas por defecto para P12
            ]);
        }
    }
}
