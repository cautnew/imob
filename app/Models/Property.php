<?php

namespace App\Models;

use App\Enums\PropertyPurpose;
use App\Enums\PropertyStatus;
use App\Enums\PropertyType;
use App\Models\Concerns\BelongsToCompany;
use Database\Factories\PropertyFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $company_id
 * @property string $title
 * @property string|null $description
 * @property PropertyPurpose $purpose
 * @property PropertyType $type
 * @property PropertyStatus $status
 * @property string $zip_code
 * @property string $street
 * @property string|null $number
 * @property string|null $complement
 * @property string $neighborhood
 * @property string $city
 * @property string $state
 * @property float|null $latitude
 * @property float|null $longitude
 * @property float $total_area
 * @property float|null $built_area
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable([
    'title', 'description', 'purpose', 'type', 'status',
    'zip_code', 'street', 'number', 'complement', 'neighborhood', 'city', 'state',
    'latitude', 'longitude',
    'total_area', 'built_area',
])]
class Property extends Model
{
    /** @use HasFactory<PropertyFactory> */
    use BelongsToCompany, HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'purpose' => PropertyPurpose::class,
            'type' => PropertyType::class,
            'status' => PropertyStatus::class,
            'latitude' => 'decimal:7',
            'longitude' => 'decimal:7',
            'total_area' => 'decimal:2',
            'built_area' => 'decimal:2',
        ];
    }

    /**
     * @return BelongsToMany<Feature, $this>
     */
    public function features(): BelongsToMany
    {
        return $this->belongsToMany(Feature::class);
    }

    /**
     * @return HasMany<PropertyAttributeValue, $this>
     */
    public function attributeValues(): HasMany
    {
        return $this->hasMany(PropertyAttributeValue::class);
    }

    /**
     * @return HasMany<PropertyPrice, $this>
     */
    public function prices(): HasMany
    {
        return $this->hasMany(PropertyPrice::class);
    }

    /**
     * @return HasMany<PropertyMedia, $this>
     */
    public function media(): HasMany
    {
        return $this->hasMany(PropertyMedia::class)->orderBy('sort_order');
    }
}
