<?php

namespace App\Http\Requests\Admin\Tools;

use App\Models\JourneyStep;
use App\Models\ToolJourney;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class JourneyStepRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        $step = $this->route('journey_step');

        $stepId = $step instanceof JourneyStep
            ? $step->getKey()
            : (is_numeric($step) ? (int) $step : null);

        $journeyId = $this->input('journey_id');

        return [
            'journey_id' => [
                'required',
                'integer',
                Rule::exists('tool_journeys', 'id'),
            ],

            'step_number' => [
                'required',
                'integer',
                'min:1',
                'max:4294967295',
                Rule::unique('journey_steps', 'step_number')
                    ->where(fn ($query) => $query->where(
                        'journey_id',
                        $journeyId
                    ))
                    ->ignore($stepId),
            ],

            'title' => [
                'required',
                'string',
                'max:160',
            ],

            'description' => [
                'nullable',
                'string',
                'max:10000',
            ],

            'what_you_need' => [
                'nullable',
                'string',
                'max:10000',
            ],

            'watch_out_for' => [
                'nullable',
                'string',
                'max:10000',
            ],

            'display_order' => [
                'required',
                'integer',
                'min:1',
                'max:4294967295',
            ],

            'status' => [
                'required',
                Rule::in(['active', 'inactive']),
            ],

            'tool_ids_present' => [
                'required',
                'accepted',
            ],

            'tool_ids' => [
                'sometimes',
                'array',
            ],

            'tool_ids.*' => [
                'required',
                'integer',
                'distinct',
                Rule::exists('tools', 'id'),
            ],
        ];
    }

    protected function prepareForValidation(): void
    {
        foreach (['title'] as $field) {
            if ($this->has($field)) {
                $this->merge([
                    $field => trim((string) $this->input($field)),
                ]);
            }
        }

        foreach (['description', 'what_you_need', 'watch_out_for'] as $field) {
            if ($this->has($field)) {
                $value = trim((string) $this->input($field));

                $this->merge([
                    $field => $value === '' ? null : $value,
                ]);
            }
        }
    }

    public function messages(): array
    {
        return [
            'journey_id.exists' => 'Please select an existing journey.',
            'step_number.unique' => 'This step number is already used in the selected journey.',
            'tool_ids.*.exists' => 'One of the selected tools does not exist.',
            'tool_ids.*.distinct' => 'A tool cannot be selected more than once.',
            'tool_ids_present.accepted' => 'Please submit the tool selection field.',
        ];
    }
}
