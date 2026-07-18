<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BillTransactionAttachRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return (bool) $this->user()?->can('update', $this->route('bill'));
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $bill = $this->route('bill');

        return [
            'transaction_id' => [
                'required', 'integer',
                Rule::exists('transactions', 'id')->where(fn ($query) => $query
                    ->where('company_id', $this->user()?->company_id)
                    ->where('lease_id', $bill->lease_id)
                    ->whereNull('bill_id')),
            ],
        ];
    }
}
