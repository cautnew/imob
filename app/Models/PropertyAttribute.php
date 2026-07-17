<?php

namespace App\Models;

use App\Enums\PropertyAttributeType;
use App\Models\Concerns\BelongsToCompany;
use Database\Factories\PropertyAttributeFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $company_id
 * @property string $name
 * @property PropertyAttributeType $type
 * @property bool $filterable
 * @property bool $comparable
 * @property bool $required
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['name', 'type', 'filterable', 'comparable', 'required'])]
class PropertyAttribute extends Model
{
    /** @use HasFactory<PropertyAttributeFactory> */
    use BelongsToCompany, HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'type' => PropertyAttributeType::class,
            'filterable' => 'boolean',
            'comparable' => 'boolean',
            'required' => 'boolean',
        ];
    }

    /**
     * @return HasMany<PropertyAttributeOption, $this>
     */
    public function options(): HasMany
    {
        return $this->hasMany(PropertyAttributeOption::class)->orderBy('order');
    }
}
