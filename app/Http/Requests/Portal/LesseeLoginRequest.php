<?php

namespace App\Http\Requests\Portal;

use App\Models\Lessee;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class LesseeLoginRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'document' => ['required', 'string', 'max:18'],
            'password' => ['required', 'string'],
        ];
    }

    /**
     * Attempt to authenticate the request's credentials against the lessee guard.
     */
    public function authenticate(): void
    {
        $normalizedDocument = preg_replace('/\D/', '', $this->string('document')->value());

        $lessee = Lessee::all(['id', 'document'])
            ->first(fn (Lessee $candidate) => preg_replace('/\D/', '', $candidate->document) === $normalizedDocument);

        if (! $lessee || ! Auth::guard('lessee')->attempt(['id' => $lessee->id, 'password' => $this->string('password')->value()], $this->boolean('remember'))) {
            throw ValidationException::withMessages([
                'document' => 'As credenciais informadas não conferem.',
            ]);
        }
    }
}
