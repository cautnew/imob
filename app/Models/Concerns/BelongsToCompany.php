<?php

namespace App\Models\Concerns;

use App\Models\Company;
use App\Models\Scopes\CompanyScope;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Attaches a model to the currently resolved tenant (see ResolveCurrentTenant).
 *
 * Applies a global scope that filters every query by the current company,
 * and auto-fills `company_id` on creation. Used by tenant-scoped business
 * models (e.g. FeatureCategory, Feature) — not applied to Company/User.
 */
trait BelongsToCompany
{
    protected static function bootBelongsToCompany(): void
    {
        static::addGlobalScope(new CompanyScope);

        static::creating(function ($model): void {
            if (! $model->company_id && app()->bound('currentCompany')) {
                $model->company_id = app('currentCompany')?->id;
            }
        });
    }

    /**
     * @return BelongsTo<Company, $this>
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }
}
