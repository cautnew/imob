<?php

namespace App\Http\Requests;

class BillUpdateRequest extends BillStoreRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return (bool) $this->user()?->can('update', $this->route('bill'));
    }
}
