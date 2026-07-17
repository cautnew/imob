<?php

namespace App\Http\Requests;

use App\Models\FeatureCategory;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class FeatureCategoryUpdateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return (bool) $this->user()?->can('update', $this->route('feature_category'));
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $target = $this->route('feature_category');
        $targetId = $target instanceof FeatureCategory ? $target->id : null;

        return [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('feature_categories')
                    ->where(fn ($query) => $query->where('company_id', $this->user()?->company_id))
                    ->ignore($targetId),
            ],
        ];
    }
}
