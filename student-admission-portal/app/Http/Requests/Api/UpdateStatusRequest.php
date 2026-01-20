<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class UpdateStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'application_id' => 'required|exists:applications,id',
            'status' => 'required|in:approved,request_info,rejected',
            'student_code' => 'required_if:status,approved',
            'reason' => 'required_if:status,request_info',
            'notes' => 'nullable|string',
            'changed_by' => 'nullable|string',
        ];
    }
}
