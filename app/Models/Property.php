<?php

namespace App\Models;

use App\Enums\PropertyPurpose;
use App\Enums\PropertyStatus;
use App\Enums\PropertyType;
use App\Models\Concerns\BelongsToCompany;
use App\Models\Concerns\HasSlug;
use Database\Factories\PropertyFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $company_id
 * @property string $title
 * @property string|null $slug
 * @property string|null $description
 * @property PropertyPurpose $purpose
 * @property PropertyType $type
 * @property PropertyStatus $status
 * @property bool $is_public
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
    'title', 'slug', 'description', 'purpose', 'type', 'status', 'is_public',
    'zip_code', 'street', 'number', 'complement', 'neighborhood', 'city', 'state',
    'latitude', 'longitude',
    'total_area', 'built_area',
])]
class Property extends Model
{
    /** @use HasFactory<PropertyFactory> */
    use BelongsToCompany, HasFactory, HasSlug;

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
            'is_public' => 'boolean',
            'latitude' => 'decimal:7',
            'longitude' => 'decimal:7',
            'total_area' => 'decimal:2',
            'built_area' => 'decimal:2',
        ];
    }

    protected function slugSourceColumn(): string
    {
        return 'title';
    }

    /**
     * @return array<string, mixed>
     */
    protected function slugScope(): array
    {
        return ['company_id' => $this->company_id];
    }

    /**
     * Scope a query to properties visible on the public portal.
     */
    public function scopePublic(Builder $query): void
    {
        $query->where('is_public', true)
            ->whereIn('status', [PropertyStatus::Available->value, PropertyStatus::Reserved->value]);
    }

    /**
     * The property's principal price for public display, following the
     * purpose-matching fallback chain (see PriceType::$purpose). Requires
     * the `prices.priceType` relation to be eager-loaded.
     */
    public function principalPrice(): ?PropertyPrice
    {
        $candidateSets = match ($this->purpose) {
            PropertyPurpose::Sale => [[PropertyPurpose::Sale]],
            PropertyPurpose::Rent => [[PropertyPurpose::Rent]],
            PropertyPurpose::SaleAndRent => [
                [PropertyPurpose::Sale, PropertyPurpose::SaleAndRent],
                [PropertyPurpose::Rent],
            ],
        };

        foreach ($candidateSets as $purposes) {
            $match = $this->prices
                ->filter(fn (PropertyPrice $price) => $price->priceType && in_array($price->priceType->purpose, $purposes, true))
                ->sortBy('amount')
                ->first();

            if ($match) {
                return $match;
            }
        }

        return $this->prices->sortBy('amount')->first();
    }

    /**
     * The raw SQL expression computing a property's principal price,
     * following the same purpose-matching fallback chain as
     * {@see principalPrice()}. Reused directly (not via the `principal_price`
     * alias) in WHERE/ORDER BY clauses — SQLite rejects HAVING without a
     * GROUP BY, which paginate()'s count-query wrapping would otherwise hit.
     */
    protected static function principalPriceSql(): string
    {
        $matched = "(SELECT MIN(pp.amount) FROM property_prices pp
                     INNER JOIN price_types pt ON pt.id = pp.price_type_id
                     WHERE pp.property_id = properties.id AND (
                       (properties.purpose IN ('venda', 'venda_aluguel') AND pt.purpose IN ('venda', 'venda_aluguel'))
                       OR (properties.purpose = 'aluguel' AND pt.purpose = 'aluguel')
                     ))";
        $fallback = '(SELECT MIN(pp2.amount) FROM property_prices pp2 WHERE pp2.property_id = properties.id)';

        return "COALESCE({$matched}, {$fallback})";
    }

    /**
     * Scope a query to add a `principal_price` column (for display/plucking)
     * following the same purpose-matching fallback chain as
     * {@see principalPrice()}.
     */
    public function scopeWithPrincipalPrice(Builder $query): void
    {
        $query->selectRaw('properties.*, '.static::principalPriceSql().' as principal_price');
    }

    /**
     * Scope a query to filter by principal price range (see
     * {@see principalPriceSql()}). Uses WHERE rather than HAVING on the
     * `principal_price` alias since SQLite requires GROUP BY before HAVING.
     *
     * Both sides of the comparison are explicitly CAST to REAL: PDO binds
     * scalar parameters as strings by default, and SQLite's query planner
     * does not reliably apply column-affinity coercion when comparing a
     * string-bound parameter against this shape of correlated-subquery
     * COALESCE expression, silently matching every row otherwise.
     */
    public function scopePrincipalPriceBetween(Builder $query, ?float $min, ?float $max): void
    {
        $sql = 'CAST('.static::principalPriceSql().' AS REAL)';

        if ($min !== null) {
            $query->whereRaw("{$sql} >= CAST(? AS REAL)", [$min]);
        }

        if ($max !== null) {
            $query->whereRaw("{$sql} <= CAST(? AS REAL)", [$max]);
        }
    }

    /**
     * Scope a query to order by principal price (see
     * {@see principalPriceSql()}).
     */
    public function scopeOrderByPrincipalPrice(Builder $query, string $direction = 'asc'): void
    {
        $query->orderByRaw(static::principalPriceSql().' '.($direction === 'desc' ? 'desc' : 'asc'));
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

    /**
     * @return BelongsToMany<Owner, $this>
     */
    public function owners(): BelongsToMany
    {
        return $this->belongsToMany(Owner::class);
    }

    /**
     * @return BelongsToMany<Lessee, $this>
     */
    public function lessees(): BelongsToMany
    {
        return $this->belongsToMany(Lessee::class);
    }

    /**
     * @return HasMany<Lease, $this>
     */
    public function leases(): HasMany
    {
        return $this->hasMany(Lease::class);
    }
}
