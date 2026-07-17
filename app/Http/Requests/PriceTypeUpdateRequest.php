<?php

namespace App\Http\Requests;

use App\Models\PriceType;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PriceTypeUpdateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return (bool) $this->user()?->can('update', $this->route('price_type'));
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'comparable' => $this->boolean('comparable'),
        ]);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $target = $this->route('price_type');
        $targetId = $target instanceof PriceType ? $target->id : null;

        return [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('price_types')
                    ->where(fn ($query) => $query->where('company_id', $this->user()?->company_id))
                    ->ignore($targetId),
            ],
            'comparable' => ['boolean'],
        ];
    }
}
