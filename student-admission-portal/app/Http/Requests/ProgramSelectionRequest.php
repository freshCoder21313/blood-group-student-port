<?php

namespace App\Http\Requests;

use App\Models\Application;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProgramSelectionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Authorized by policy in controller
    }

    public function rules(): array
    {
        /** @var Application $application */
        $application = $this->route('application');

        // If status is draft, allow nullable. If submitted/pending, required.
        // We use 'sometimes' and logic based on status.
        // Actually, for the 'updateProgram' action, we are typically in draft mode.
        // But if we were to submit later, it would be required.
        // The story says: "Given I am editing a "Draft" application... Then the system saves the change (field is nullable) without validation error"

        $isDraft = $application->status === 'draft';

        return [
            'program_id' => [
                $isDraft ? 'nullable' : 'required',
                Rule::exists('programs', 'id')->where('is_active', true),
            ],
        ];
    }
}
