<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ParentDetailsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $application = $this->route('application');
        $isDraft = $application ? $application->status === 'draft' : true;

        $rules = [
            'guardian_name' => ['string', 'max:100'],
            'guardian_phone' => ['string', 'max:20'],
            'relationship' => ['string', 'max:50'],
            'guardian_email' => ['nullable', 'email', 'max:100'],
        ];

        if ($isDraft) {
            foreach ($rules as $field => $fieldRules) {
                if (!in_array('nullable', $fieldRules)) {
                    array_unshift($rules[$field], 'nullable');
                }
            }
        } else {
            foreach ($rules as $field => $fieldRules) {
                if (!in_array('nullable', $fieldRules)) {
                    array_unshift($rules[$field], 'required');
                }
            }
        }

        return $rules;
    }
}
