<?php

namespace App\Http\Requests;

use App\Enums\TransactionType;
use App\Models\TransactionCategory;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class TransactionCategoryStoreRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return (bool) $this->user()?->can('create', TransactionCategory::class);
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
                Rule::unique('transaction_categories')->where(fn ($query) => $query
                    ->where('company_id', $this->user()?->company_id)),
            ],
            'type' => ['required', Rule::enum(TransactionType::class)],
        ];
    }
}
