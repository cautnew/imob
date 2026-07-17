<?php

namespace App\Http\Requests;

use App\Enums\PropertyAttributeType;
use App\Models\PropertyAttribute;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PropertyAttributeUpdateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return (bool) $this->user()?->can('update', $this->route('property_attribute'));
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'filterable' => $this->boolean('filterable'),
            'comparable' => $this->boolean('comparable'),
            'required' => $this->boolean('required'),
        ]);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $target = $this->route('property_attribute');
        $targetId = $target instanceof PropertyAttribute ? $target->id : null;

        $hasOptions = PropertyAttributeType::tryFrom((string) $this->input('type'))?->hasOptions() ?? false;

        return [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('property_attributes')
                    ->where(fn ($query) => $query->where('company_id', $this->user()?->company_id))
                    ->ignore($targetId),
            ],
            'type' => ['required', Rule::enum(PropertyAttributeType::class)],
            'filterable' => ['boolean'],
            'comparable' => ['boolean'],
            'required' => ['boolean'],
            'options' => Rule::when($hasOptions, ['required', 'array', 'min:1'], ['prohibited']),
            'options.*.value' => ['required', 'string', 'max:255', 'distinct'],
        ];
    }
}
