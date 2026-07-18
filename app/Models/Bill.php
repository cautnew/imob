<?php

namespace App\Models;

use App\Enums\BillStatus;
use App\Models\Concerns\BelongsToCompany;
use Database\Factories\BillFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $company_id
 * @property int $lease_id
 * @property BillStatus $status
 * @property Carbon $due_date
 * @property Carbon|null $paid_date
 * @property string|null $description
 * @property string|null $disk
 * @property string|null $path
 * @property string|null $original_filename
 * @property string|null $mime_type
 * @property int|null $size
 * @property Carbon|null $overdue_notified_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable([
    'lease_id', 'due_date', 'description', 'status', 'paid_date',
    'disk', 'path', 'original_filename', 'mime_type', 'size', 'overdue_notified_at',
])]
class Bill extends Model
{
    /** @use HasFactory<BillFactory> */
    use BelongsToCompany, HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'due_date' => 'date',
            'paid_date' => 'date',
            'status' => BillStatus::class,
            'size' => 'integer',
            'overdue_notified_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<Lease, $this>
     */
    public function lease(): BelongsTo
    {
        return $this->belongsTo(Lease::class);
    }

    /**
     * @return HasMany<Transaction, $this>
     */
    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    /**
     * @return HasMany<BillEvent, $this>
     */
    public function events(): HasMany
    {
        return $this->hasMany(BillEvent::class)->orderByDesc('occurred_on')->orderByDesc('id');
    }

    /**
     * The sum of the amounts of every transaction linked to this bill.
     */
    public function totalAmount(): string
    {
        return number_format((float) $this->transactions()->sum('amount'), 2, '.', '');
    }

    /**
     * The bill's status, with "pendente" past its due date reported as "vencido".
     * This is computed on read rather than persisted — the stored status only ever
     * moves between Pending and Paid (see BillStatusController).
     */
    public function effectiveStatus(): BillStatus
    {
        if ($this->status === BillStatus::Pending && $this->due_date->isPast()) {
            return BillStatus::Overdue;
        }

        return $this->status;
    }

    /**
     * Scope a query to pending bills that are already past their due date.
     *
     * @param  Builder<Bill>  $query
     * @return Builder<Bill>
     */
    public function scopeOverdue(Builder $query): Builder
    {
        return $query->where('status', BillStatus::Pending)
            ->whereDate('due_date', '<', today());
    }

    /**
     * Scope a query to pending bills that are not yet past their due date.
     *
     * @param  Builder<Bill>  $query
     * @return Builder<Bill>
     */
    public function scopeOpen(Builder $query): Builder
    {
        return $query->where('status', BillStatus::Pending)
            ->whereDate('due_date', '>=', today());
    }
}
