<?php

namespace App\Models;

use App\Enums\PropertyPurpose;
use App\Models\Concerns\BelongsToCompany;
use Database\Factories\PriceTypeFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $company_id
 * @property string $name
 * @property PropertyPurpose|null $purpose
 * @property bool $comparable
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['name', 'purpose', 'comparable'])]
class PriceType extends Model
{
    /** @use HasFactory<PriceTypeFactory> */
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
            'comparable' => 'boolean',
        ];
    }

    /**
     * @return HasMany<PropertyPrice, $this>
     */
    public function prices(): HasMany
    {
        return $this->hasMany(PropertyPrice::class);
    }
}
