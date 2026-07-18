<?php

namespace App\Models;

use App\Enums\MaritalStatus;
use App\Models\Concerns\BelongsToCompany;
use Database\Factories\LesseeFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $company_id
 * @property string $name
 * @property Carbon|null $birth_date
 * @property MaritalStatus|null $marital_status
 * @property string|null $occupation
 * @property string $document
 * @property string|null $rg
 * @property string|null $rg_issuer
 * @property string $phone
 * @property string|null $mobile
 * @property string|null $email
 * @property string|null $password
 * @property string|null $remember_token
 * @property string $zip_code
 * @property string $street
 * @property string|null $number
 * @property string|null $complement
 * @property string $neighborhood
 * @property string $city
 * @property string $state
 * @property string|null $monthly_income
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable([
    'name', 'birth_date', 'marital_status', 'occupation',
    'document', 'rg', 'rg_issuer',
    'phone', 'mobile', 'email', 'password',
    'zip_code', 'street', 'number', 'complement', 'neighborhood', 'city', 'state',
    'monthly_income',
])]
#[Hidden(['password', 'remember_token'])]
class Lessee extends Authenticatable
{
    /** @use HasFactory<LesseeFactory> */
    use BelongsToCompany, HasFactory, Notifiable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'birth_date' => 'date',
            'marital_status' => MaritalStatus::class,
            'monthly_income' => 'decimal:2',
            'password' => 'hashed',
        ];
    }

    /**
     * The properties this lessee currently rents or has rented.
     *
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

    /**
     * @return HasMany<BillReceipt, $this>
     */
    public function receipts(): HasMany
    {
        return $this->hasMany(BillReceipt::class);
    }
}
