<?php

namespace App\Models;

use App\Enums\TransactionStatus;
use App\Models\Concerns\BelongsToCompany;
use Database\Factories\TransactionFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $company_id
 * @property int $property_id
 * @property int|null $lease_id
 * @property int $transaction_category_id
 * @property string $description
 * @property string $amount
 * @property Carbon $due_date
 * @property Carbon|null $paid_date
 * @property TransactionStatus $status
 * @property string|null $notes
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable([
    'property_id', 'lease_id', 'transaction_category_id',
    'description', 'amount', 'due_date', 'notes',
    'status', 'paid_date',
])]
class Transaction extends Model
{
    /** @use HasFactory<TransactionFactory> */
    use BelongsToCompany, HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'due_date' => 'date',
            'paid_date' => 'date',
            'status' => TransactionStatus::class,
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
     * @return BelongsTo<Lease, $this>
     */
    public function lease(): BelongsTo
    {
        return $this->belongsTo(Lease::class);
    }

    /**
     * @return BelongsTo<TransactionCategory, $this>
     */
    public function transactionCategory(): BelongsTo
    {
        return $this->belongsTo(TransactionCategory::class);
    }

    /**
     * The transaction's status, with "pendente" past its due date reported as "vencido".
     * This is computed on read rather than persisted — the stored status only ever
     * moves between Pending and Paid (see TransactionController::toggleStatus).
     */
    public function effectiveStatus(): TransactionStatus
    {
        if ($this->status === TransactionStatus::Pending && $this->due_date->isPast()) {
            return TransactionStatus::Overdue;
        }

        return $this->status;
    }

    /**
     * Scope a query to pending transactions that are already past their due date.
     *
     * @param  Builder<Transaction>  $query
     * @return Builder<Transaction>
     */
    public function scopeOverdue(Builder $query): Builder
    {
        return $query->where('status', TransactionStatus::Pending)
            ->whereDate('due_date', '<', today());
    }

    /**
     * Scope a query to pending transactions that are not yet past their due date.
     *
     * @param  Builder<Transaction>  $query
     * @return Builder<Transaction>
     */
    public function scopeOpen(Builder $query): Builder
    {
        return $query->where('status', TransactionStatus::Pending)
            ->whereDate('due_date', '>=', today());
    }
}
