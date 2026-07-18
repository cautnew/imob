<?php

namespace App\Http\Requests;

use App\Enums\MaritalStatus;
use App\Models\Lessee;
use App\Rules\CpfCnpj;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class LesseeStoreRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return (bool) $this->user()?->can('create', Lessee::class);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            ...$this->personalRules(),
            ...$this->documentRules(),
            ...$this->contactRules(),
            ...$this->addressRules(),
            ...$this->incomeRules(),
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
    protected function personalRules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'birth_date' => ['nullable', 'date'],
            'marital_status' => ['nullable', Rule::enum(MaritalStatus::class)],
            'occupation' => ['nullable', 'string', 'max:255'],
        ];
    }

    /**
     * @return array<string, array<mixed>>
     */
    protected function documentRules(): array
    {
        return [
            'document' => [
                'required', 'string', 'max:18', new CpfCnpj,
                Rule::unique('lessees', 'document')
                    ->where(fn ($query) => $query->where('company_id', $this->user()?->company_id))
                    ->ignore($this->route('lessee')),
            ],
            'rg' => ['nullable', 'string', 'max:20'],
            'rg_issuer' => ['nullable', 'string', 'max:20'],
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
    protected function incomeRules(): array
    {
        return [
            'monthly_income' => ['nullable', 'numeric', 'min:0', 'max:99999999.99'],
        ];
    }
}
