<?php

namespace App\Models;

use Database\Factories\PropertyMediaFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $property_id
 * @property string $disk
 * @property string $path
 * @property string|null $original_filename
 * @property string|null $mime_type
 * @property int|null $size
 * @property string|null $caption
 * @property bool $is_cover
 * @property int $sort_order
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable([
    'disk', 'path', 'original_filename', 'mime_type', 'size', 'caption', 'is_cover', 'sort_order',
])]
class PropertyMedia extends Model
{
    /** @use HasFactory<PropertyMediaFactory> */
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_cover' => 'boolean',
            'size' => 'integer',
            'sort_order' => 'integer',
        ];
    }

    /**
     * @return BelongsTo<Property, $this>
     */
    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }
}
