<?php

namespace App\Http\Requests;

use App\Enums\PriceFrequency;
use App\Enums\PropertyAttributeType;
use App\Enums\PropertyPurpose;
use App\Enums\PropertyStatus;
use App\Enums\PropertyType;
use App\Models\Property;
use App\Models\PropertyAttribute;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PropertyStoreRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return (bool) $this->user()?->can('create', Property::class);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            ...$this->basicRules(),
            ...$this->addressRules(),
            ...$this->geolocationRules(),
            ...$this->pricesRules(),
            ...$this->areaRules(),
            'features' => ['sometimes', 'array'],
            'features.*' => [
                'integer',
                Rule::exists('features', 'id')->where(fn ($query) => $query
                    ->where('company_id', $this->user()?->company_id)),
            ],
            ...$this->attributeRules(),
        ];
    }

    /**
     * @return array<string, array<mixed>>
     */
    protected function basicRules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'purpose' => ['required', Rule::enum(PropertyPurpose::class)],
            'type' => ['required', Rule::enum(PropertyType::class)],
            'status' => ['required', Rule::enum(PropertyStatus::class)],
        ];
    }

    /**
     * @return array<string, array<mixed>>
     */
    protected function addressRules(): array
    {
        return [
            'zip_code' => ['required', 'string', 'max:9'],
            'street' => ['required', 'string', 'max:255'],
            'number' => ['nullable', 'string', 'max:20'],
            'complement' => ['nullable', 'string', 'max:255'],
            'neighborhood' => ['required', 'string', 'max:255'],
            'city' => ['required', 'string', 'max:255'],
            'state' => ['required', 'string', 'size:2'],
        ];
    }

    /**
     * @return array<string, array<mixed>>
     */
    protected function geolocationRules(): array
    {
        return [
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
        ];
    }

    /**
     * @return array<string, array<mixed>>
     */
    protected function pricesRules(): array
    {
        return [
            'prices' => ['required', 'array', 'min:1'],
            'prices.*.price_type_id' => [
                'required',
                'integer',
                Rule::exists('price_types', 'id')->where(fn ($query) => $query
                    ->where('company_id', $this->user()?->company_id)),
            ],
            'prices.*.amount' => ['required', 'numeric', 'min:0'],
            'prices.*.frequency' => ['required', Rule::enum(PriceFrequency::class)],
        ];
    }

    /**
     * @return array<string, array<mixed>>
     */
    protected function areaRules(): array
    {
        return [
            'total_area' => ['required', 'numeric', 'min:0'],
            'built_area' => ['nullable', 'numeric', 'min:0'],
        ];
    }

    /**
     * Build validation rules for the company's dynamic property attribute catalog.
     *
     * @return array<string, mixed>
     */
    protected function attributeRules(): array
    {
        $rules = ['attributes' => ['sometimes', 'array']];

        $attributes = PropertyAttribute::query()
            ->where('company_id', $this->user()?->company_id)
            ->with('options')
            ->get();

        foreach ($attributes as $attribute) {
            $key = "attributes.{$attribute->id}";
            $required = $attribute->required ? 'required' : 'nullable';
            $optionIds = $attribute->options->pluck('id');

            $rules[$key] = match ($attribute->type) {
                PropertyAttributeType::Text => [$required, 'string', 'max:255'],
                PropertyAttributeType::Integer => [$required, 'integer'],
                PropertyAttributeType::Decimal => [$required, 'numeric'],
                PropertyAttributeType::Boolean => [$required, 'boolean'],
                PropertyAttributeType::Date => [$required, 'date'],
                PropertyAttributeType::Select => [$required, Rule::in($optionIds)],
                PropertyAttributeType::Multiselect => [$required, 'array'],
            };

            if ($attribute->type === PropertyAttributeType::Multiselect) {
                $rules["{$key}.*"] = [Rule::in($optionIds)];
            }
        }

        return $rules;
    }
}
