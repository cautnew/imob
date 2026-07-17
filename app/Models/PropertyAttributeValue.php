<?php

namespace App\Models;

use Database\Factories\PropertyAttributeValueFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $property_id
 * @property int $property_attribute_id
 * @property int|null $property_attribute_option_id
 * @property string|null $value
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['property_attribute_id', 'property_attribute_option_id', 'value'])]
class PropertyAttributeValue extends Model
{
    /** @use HasFactory<PropertyAttributeValueFactory> */
    use HasFactory;

    /**
     * @return BelongsTo<Property, $this>
     */
    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }

    /**
     * @return BelongsTo<PropertyAttribute, $this>
     */
    public function propertyAttribute(): BelongsTo
    {
        return $this->belongsTo(PropertyAttribute::class);
    }

    /**
     * @return BelongsTo<PropertyAttributeOption, $this>
     */
    public function propertyAttributeOption(): BelongsTo
    {
        return $this->belongsTo(PropertyAttributeOption::class);
    }
}
