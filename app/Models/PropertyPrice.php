<?php

namespace App\Models;

use App\Enums\PriceFrequency;
use Database\Factories\PropertyPriceFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $property_id
 * @property int $price_type_id
 * @property float $amount
 * @property PriceFrequency $frequency
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['price_type_id', 'amount', 'frequency'])]
class PropertyPrice extends Model
{
    /** @use HasFactory<PropertyPriceFactory> */
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'frequency' => PriceFrequency::class,
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
     * @return BelongsTo<PriceType, $this>
     */
    public function priceType(): BelongsTo
    {
        return $this->belongsTo(PriceType::class);
    }
}
