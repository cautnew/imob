<?php

namespace App\Http\Requests;

use App\Enums\LeaseAdjustmentIndex;
use App\Enums\LeaseRenewalType;
use App\Models\Lease;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class LeaseStoreRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return (bool) $this->user()?->can('create', Lease::class);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'property_id' => [
                'required', 'integer',
                Rule::exists('properties', 'id')->where(fn ($query) => $query
                    ->where('company_id', $this->user()?->company_id)),
            ],
            'owner_id' => [
                'required', 'integer',
                Rule::exists('owners', 'id')->where(fn ($query) => $query
                    ->where('company_id', $this->user()?->company_id)),
            ],
            'lessee_id' => [
                'required', 'integer',
                Rule::exists('lessees', 'id')->where(fn ($query) => $query
                    ->where('company_id', $this->user()?->company_id)),
            ],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after:start_date'],
            'rent_amount' => ['required', 'numeric', 'min:0.01'],
            'adjustment_index' => ['required', Rule::enum(LeaseAdjustmentIndex::class)],
            'adjustment_interval_months' => ['required', 'integer', 'min:1', 'max:60'],
            'renewal_type' => ['required', Rule::enum(LeaseRenewalType::class)],
            'notes' => ['nullable', 'string'],
        ];
    }
}
