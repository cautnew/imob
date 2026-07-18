<?php

namespace App\Models;

use App\Enums\LeaseAdjustmentIndex;
use App\Enums\LeaseRenewalType;
use App\Enums\LeaseStatus;
use App\Models\Concerns\BelongsToCompany;
use Database\Factories\LeaseFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $company_id
 * @property int $property_id
 * @property int $owner_id
 * @property int $lessee_id
 * @property Carbon $start_date
 * @property Carbon $end_date
 * @property string $rent_amount
 * @property LeaseAdjustmentIndex $adjustment_index
 * @property int $adjustment_interval_months
 * @property Carbon|null $last_adjustment_date
 * @property LeaseRenewalType $renewal_type
 * @property LeaseStatus $status
 * @property string|null $notes
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable([
    'property_id', 'owner_id', 'lessee_id',
    'start_date', 'end_date',
    'rent_amount', 'adjustment_index', 'adjustment_interval_months', 'last_adjustment_date',
    'renewal_type', 'status', 'notes',
])]
class Lease extends Model
{
    /** @use HasFactory<LeaseFactory> */
    use BelongsToCompany, HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
            'rent_amount' => 'decimal:2',
            'adjustment_index' => LeaseAdjustmentIndex::class,
            'last_adjustment_date' => 'date',
            'renewal_type' => LeaseRenewalType::class,
            'status' => LeaseStatus::class,
        ];
    }

    /**
     * @return BelongsTo<Property, $this>
     */
    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }

    /**
     * @return BelongsTo<Owner, $this>
     */
    public function owner(): BelongsTo
    {
        return $this->belongsTo(Owner::class);
    }

    /**
     * @return BelongsTo<Lessee, $this>
     */
    public function lessee(): BelongsTo
    {
        return $this->belongsTo(Lessee::class);
    }

    /**
     * @return HasMany<LeaseEvent, $this>
     */
    public function events(): HasMany
    {
        return $this->hasMany(LeaseEvent::class)->orderByDesc('occurred_on')->orderByDesc('id');
    }

    /**
     * @return HasMany<LeaseDocument, $this>
     */
    public function documents(): HasMany
    {
        return $this->hasMany(LeaseDocument::class)->orderByDesc('created_at');
    }
}
