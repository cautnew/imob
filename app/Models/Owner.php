<?php

namespace App\Models;

use App\Enums\BankAccountType;
use App\Models\Concerns\BelongsToCompany;
use Database\Factories\OwnerFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $company_id
 * @property string $name
 * @property string $document
 * @property string $phone
 * @property string|null $mobile
 * @property string|null $email
 * @property string $zip_code
 * @property string $street
 * @property string|null $number
 * @property string|null $complement
 * @property string $neighborhood
 * @property string $city
 * @property string $state
 * @property string|null $bank_name
 * @property string|null $bank_agency
 * @property string|null $bank_account
 * @property BankAccountType|null $bank_account_type
 * @property string|null $pix_key
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable([
    'name', 'document', 'phone', 'mobile', 'email',
    'zip_code', 'street', 'number', 'complement', 'neighborhood', 'city', 'state',
    'bank_name', 'bank_agency', 'bank_account', 'bank_account_type', 'pix_key',
])]
class Owner extends Model
{
    /** @use HasFactory<OwnerFactory> */
    use BelongsToCompany, HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'bank_account_type' => BankAccountType::class,
        ];
    }

    /**
     * @return BelongsToMany<Property, $this>
     */
    public function properties(): BelongsToMany
    {
        return $this->belongsToMany(Property::class);
    }

    /**
     * @return HasMany<Lease, $this>
     */
    public function leases(): HasMany
    {
        return $this->hasMany(Lease::class);
    }
}
