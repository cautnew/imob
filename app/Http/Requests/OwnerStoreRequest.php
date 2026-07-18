<?php

namespace App\Http\Requests;

use App\Enums\BankAccountType;
use App\Models\Owner;
use App\Rules\CpfCnpj;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class OwnerStoreRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return (bool) $this->user()?->can('create', Owner::class);
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
            ...$this->contactRules(),
            ...$this->addressRules(),
            ...$this->bankRules(),
            'property_ids' => ['sometimes', 'array'],
            'property_ids.*' => [
                'integer',
                Rule::exists('properties', 'id')->where(fn ($query) => $query
                    ->where('company_id', $this->user()?->company_id)),
            ],
        ];
    }

    /**
     * @return array<string, array<mixed>>
     */
    protected function basicRules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'document' => [
                'required', 'string', 'max:18', new CpfCnpj,
                Rule::unique('owners', 'document')
                    ->where(fn ($query) => $query->where('company_id', $this->user()?->company_id))
                    ->ignore($this->route('owner')),
            ],
        ];
    }

    /**
     * @return array<string, array<mixed>>
     */
    protected function contactRules(): array
    {
        return [
            'phone' => ['required', 'string', 'max:20'],
            'mobile' => ['nullable', 'string', 'max:20'],
            'email' => ['nullable', 'email', 'max:255'],
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
    protected function bankRules(): array
    {
        return [
            'bank_name' => ['nullable', 'string', 'max:255'],
            'bank_agency' => ['nullable', 'string', 'max:20'],
            'bank_account' => ['nullable', 'string', 'max:20'],
            'bank_account_type' => ['nullable', Rule::enum(BankAccountType::class)],
            'pix_key' => ['nullable', 'string', 'max:255'],
        ];
    }
}
