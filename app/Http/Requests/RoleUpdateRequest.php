<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Role;

class RoleUpdateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return (bool) $this->user()?->can('update', $this->route('role'));
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $target = $this->route('role');
        $targetId = $target instanceof Role ? $target->id : null;

        return [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('roles')
                    ->where(fn ($query) => $query
                        ->where('company_id', $this->user()?->company_id)
                        ->where('guard_name', 'web'))
                    ->ignore($targetId),
            ],
            'permissions' => ['array'],
            'permissions.*' => ['integer', Rule::exists('permissions', 'id')],
        ];
    }
}
