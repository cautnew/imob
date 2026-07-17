<?php

namespace App\Models;

use Database\Factories\PropertyAttributeOptionFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $property_attribute_id
 * @property string $value
 * @property int $order
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['value', 'order'])]
class PropertyAttributeOption extends Model
{
    /** @use HasFactory<PropertyAttributeOptionFactory> */
    use HasFactory;

    /**
     * @return BelongsTo<PropertyAttribute, $this>
     */
    public function propertyAttribute(): BelongsTo
    {
        return $this->belongsTo(PropertyAttribute::class);
    }
}
