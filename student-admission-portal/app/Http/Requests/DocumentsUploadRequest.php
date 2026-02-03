<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class DocumentsUploadRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'national_id' => 'nullable|file|mimes:jpeg,png,pdf|max:5120',
            'transcript' => 'nullable|file|mimes:jpeg,png,pdf|max:5120',
            'health_certificate' => 'nullable|file|mimes:jpeg,png,pdf|max:5120',
        ];
    }
}
