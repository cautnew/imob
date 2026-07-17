<?php

namespace App\Http\Requests;

use App\Models\Feature;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class FeatureStoreRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return (bool) $this->user()?->can('create', Feature::class);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('features')->where(fn ($query) => $query
                    ->where('company_id', $this->user()?->company_id)),
            ],
            'feature_category_id' => [
                'required',
                'integer',
                Rule::exists('feature_categories', 'id')->where(fn ($query) => $query
                    ->where('company_id', $this->user()?->company_id)),
            ],
        ];
    }
}
