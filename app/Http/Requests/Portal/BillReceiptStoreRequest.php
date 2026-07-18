<?php

namespace App\Http\Requests\Portal;

use App\Models\Bill;
use App\Models\Lessee;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class BillReceiptStoreRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $lessee = $this->user();
        $bill = $this->route('bill');

        if (! $lessee instanceof Lessee || ! $bill instanceof Bill) {
            return false;
        }

        return $bill->lease->lessee_id === $lessee->id;
    }

    /**
     * A bill belonging to another lessee should 404, not 403 — never
     * confirm to a lessee that a given bill ID exists at all.
     */
    protected function failedAuthorization(): void
    {
        throw new NotFoundHttpException;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'file' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:10240'],
        ];
    }
}
