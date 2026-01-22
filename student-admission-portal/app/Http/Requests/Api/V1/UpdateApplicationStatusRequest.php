<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateApplicationStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'application_id' => ['required', 'integer', 'exists:applications,id'],
            'status' => ['required', 'string', Rule::in(['approved', 'rejected', 'request_info'])],
            'comment' => ['nullable', 'string'],
            'student_code' => ['nullable', 'string', 'max:50'],
        ];
    }
}
