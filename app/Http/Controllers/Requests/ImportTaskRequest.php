<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ImportTaskRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Ubah jadi true agar user yang login bisa akses
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'import_file' => 'required_without:import_url|file|mimes:csv,txt,xlsx,xls|max:10240',
            'import_url' => 'required_without:import_file|url',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'import_file.required_without' => 'File import harus diisi jika tidak menggunakan URL',
            'import_file.file' => 'File harus berupa file yang valid',
            'import_file.mimes' => 'Format file harus CSV, Excel (XLSX, XLS), atau TXT',
            'import_file.max' => 'File tidak boleh lebih dari 10MB',
            'import_url.required_without' => 'URL harus diisi jika tidak menggunakan file upload',
            'import_url.url' => 'URL harus berupa format URL yang valid',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'import_file' => 'file import',
            'import_url' => 'URL import',
        ];
    }
}