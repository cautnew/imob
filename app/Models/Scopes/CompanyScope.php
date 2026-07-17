<?php

namespace App\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

/**
 * @implements Scope<Model>
 */
class CompanyScope implements Scope
{
    /**
     * Filter the query to the currently resolved tenant, when one is bound.
     */
    public function apply(Builder $builder, Model $model): void
    {
        if (! app()->bound('currentCompany')) {
            return;
        }

        if (! $currentCompany = app('currentCompany')) {
            return;
        }

        $builder->where($model->qualifyColumn('company_id'), $currentCompany->id);
    }
}
