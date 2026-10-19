<?php

namespace App\Http\Requests\Admin\Tools;

use App\Models\Tool;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ToolRequest extends FormRequest
{
    public function authorize(): bool
{
    return $this->user()?->role === 'admin';
}

    public function rules(): array
    {
        $tool = $this->route('tool');

        $toolId = $tool instanceof Tool
            ? $tool->getKey()
            : (is_numeric($tool) ? (int) $tool : null);

        return [
            'name' => [
                'required',
                'string',
                'max:120',
            ],

            'slug' => [
                'nullable',
                'string',
                'max:140',
                'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/',
                Rule::unique('tools', 'slug')->ignore($toolId),
            ],

            'short_description' => [
                'required',
                'string',
                'max:200',
            ],

            'icon' => [
                'required',
                'string',
                'max:60',
            ],

            'category' => [
                'required',
                'string',
                'max:40',
            ],

            'type' => [
                'required',
                'string',
                'max:30',
            ],

            'status' => [
                'required',
                Rule::in([
                    Tool::STATUS_ACTIVE,
                    Tool::STATUS_PLANNED,
                    Tool::STATUS_INACTIVE,
                ]),
            ],

            'display_order' => [
                'sometimes',
                'required',
                'integer',
                'min:1',
                'max:4294967295',
            ],

            'page_route' => [
                'required',
                'string',
                'max:255',
            ],

            'show_on_toolkit_home' => [
                'sometimes',
                'boolean',
            ],

            'journey_ids' => [
                'sometimes',
                'array',
            ],

            'journey_ids.*' => [
                'required',
                'integer',
                'distinct',
                Rule::exists('tool_journeys', 'id'),
            ],
        ];
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('name')) {
            $this->merge([
                'name' => trim((string) $this->input('name')),
            ]);
        }

        if ($this->has('slug')) {
            $slug = trim((string) $this->input('slug'));

            $this->merge([
                'slug' => $slug === '' ? null : $slug,
            ]);
        }

        if ($this->has('show_on_toolkit_home')) {
            $this->merge([
                'show_on_toolkit_home' =>
                    $this->boolean('show_on_toolkit_home'),
            ]);
        }
    }

    public function messages(): array
    {
        return [
            'slug.unique' => 'This tool slug is already in use.',
            'slug.regex' => 'The slug may contain lowercase letters, numbers and hyphens only.',
            'journey_ids.*.exists' => 'A selected journey does not exist.',
            'journey_ids.*.distinct' => 'A journey cannot be selected more than once.',
        ];
    }
}
