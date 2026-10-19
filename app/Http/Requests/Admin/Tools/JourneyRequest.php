<?php

namespace App\Http\Requests\Admin\Tools;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;

class JourneyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->role === 'admin';
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'name' => is_string($this->name)
                ? trim($this->name)
                : $this->name,

            'slug' => filled($this->slug)
                ? Str::slug($this->slug)
                : Str::slug((string) $this->name),

            'short_label' => filled($this->short_label)
                ? trim((string) $this->short_label)
                : null,

            'description' => filled($this->description)
                ? trim((string) $this->description)
                : null,
        ]);
    }

    public function rules(): array
    {
        $journey = $this->route('tool_journey');
        $journeyId = is_object($journey)
            ? $journey->getKey()
            : $journey;

        return [
            'name' => ['required', 'string', 'max:120'],

            'slug' => [
                'required',
                'string',
                'max:140',
                'alpha_dash',
                Rule::unique('tool_journeys', 'slug')->ignore($journeyId),
            ],

            'short_label' => ['nullable', 'string', 'max:80'],
            'description' => ['nullable', 'string', 'max:10000'],
            'display_order' => [
                'required',
                'integer',
                'min:1',
                'max:4294967295',
            ],
            'status' => ['required', Rule::in(['active', 'inactive'])],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Journey name is required.',
            'slug.required' => 'Journey slug is required.',
            'slug.unique' => 'This journey slug already exists.',
            'display_order.required' => 'Display order is required.',
            'status.in' => 'Select a valid journey status.',
        ];
    }
}
