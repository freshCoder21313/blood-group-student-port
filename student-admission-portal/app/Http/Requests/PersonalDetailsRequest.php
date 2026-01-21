<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PersonalDetailsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $application = $this->route('application');
        // If application is not draft, we might enforce strictness, but for now mostly draft.
        
        $isDraft = $application ? $application->status === 'draft' : true;

        $rules = [
            'first_name' => ['string', 'max:100'],
            'middle_name' => ['nullable', 'string', 'max:100'],
            'last_name' => ['string', 'max:100'],
            'date_of_birth' => ['date', 'before:-16 years'],
            'gender' => ['string', Rule::in(['male', 'female', 'other'])],
            'nationality' => ['string', 'max:100'],
            'national_id' => ['string', 'max:50'],
            'passport_number' => ['nullable', 'string', 'max:50'],
            'address' => ['string'],
            'city' => ['string', 'max:100'],
            'county' => ['string', 'max:100'],
            'postal_code' => ['nullable', 'string', 'max:20'],
        ];

        // If in draft mode, all fields are optional (nullable)
        if ($isDraft) {
            foreach ($rules as $field => $fieldRules) {
                // Ensure nullable is present
                if (!in_array('nullable', $fieldRules)) {
                    array_unshift($rules[$field], 'nullable');
                }
            }
        } else {
            // If not draft (e.g. somehow editing after submit, or if we use this for strict validation), make them required
            foreach ($rules as $field => $fieldRules) {
                if (!in_array('nullable', $fieldRules)) {
                    array_unshift($rules[$field], 'required');
                }
            }
        }

        return $rules;
    }
}
