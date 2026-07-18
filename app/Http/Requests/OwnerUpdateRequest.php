<?php

namespace App\Http\Requests;

class OwnerUpdateRequest extends OwnerStoreRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return (bool) $this->user()?->can('update', $this->route('owner'));
    }
}
