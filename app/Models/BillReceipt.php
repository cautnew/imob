<?php

namespace App\Models;

use App\Enums\BillReceiptStatus;
use App\Models\Concerns\BelongsToCompany;
use Database\Factories\BillReceiptFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $company_id
 * @property int $bill_id
 * @property int $lessee_id
 * @property BillReceiptStatus $status
 * @property string $disk
 * @property string $path
 * @property string $original_filename
 * @property string $mime_type
 * @property int $size
 * @property int|null $reviewed_by
 * @property Carbon|null $reviewed_at
 * @property string|null $rejection_reason
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable([
    'bill_id', 'lessee_id', 'status',
    'disk', 'path', 'original_filename', 'mime_type', 'size',
    'reviewed_by', 'reviewed_at', 'rejection_reason',
])]
class BillReceipt extends Model
{
    /** @use HasFactory<BillReceiptFactory> */
    use BelongsToCompany, HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => BillReceiptStatus::class,
            'size' => 'integer',
            'reviewed_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<Bill, $this>
     */
    public function bill(): BelongsTo
    {
        return $this->belongsTo(Bill::class);
    }

    /**
     * @return BelongsTo<Lessee, $this>
     */
    public function lessee(): BelongsTo
    {
        return $this->belongsTo(Lessee::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }
}
