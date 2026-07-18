<?php

namespace App\Http\Requests;

use App\Models\Transaction;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class TransactionStoreRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return (bool) $this->user()?->can('create', Transaction::class);
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
            'lease_id' => [
                'nullable', 'integer',
                Rule::exists('leases', 'id')->where(fn ($query) => $query
                    ->where('company_id', $this->user()?->company_id)),
            ],
            'transaction_category_id' => [
                'required', 'integer',
                Rule::exists('transaction_categories', 'id')->where(fn ($query) => $query
                    ->where('company_id', $this->user()?->company_id)),
            ],
            'description' => ['required', 'string', 'max:255'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'due_date' => ['required', 'date'],
            'notes' => ['nullable', 'string'],
        ];
    }
}
