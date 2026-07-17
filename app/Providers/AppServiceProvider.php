<?php

namespace App\Providers;

use App\Models\Company;
use App\Models\Feature;
use App\Models\FeatureCategory;
use App\Models\PriceType;
use App\Models\Property;
use App\Models\PropertyAttribute;
use App\Models\User;
use App\Observers\CompanyObserver;
use App\Policies\FeatureCategoryPolicy;
use App\Policies\FeaturePolicy;
use App\Policies\PermissionPolicy;
use App\Policies\PriceTypePolicy;
use App\Policies\PropertyAttributePolicy;
use App\Policies\PropertyPolicy;
use App\Policies\RolePolicy;
use App\Policies\UserPolicy;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->configureDefaults();

        Company::observe(CompanyObserver::class);

        $this->configureAuthorization();
    }

    /**
     * Configure default behaviors for production-ready applications.
     */
    protected function configureDefaults(): void
    {
        Date::use(CarbonImmutable::class);

        DB::prohibitDestructiveCommands(
            app()->isProduction(),
        );

        Password::defaults(fn (): ?Password => app()->isProduction()
            ? Password::min(12)
                ->mixedCase()
                ->letters()
                ->numbers()
                ->symbols()
                ->uncompromised()
            : null,
        );
    }

    /**
     * Configure authorization policies and gates.
     */
    protected function configureAuthorization(): void
    {
        Gate::before(fn (User $user) => $user->is_owner ? true : null);

        Gate::policy(User::class, UserPolicy::class);
        Gate::policy(Role::class, RolePolicy::class);
        Gate::policy(Permission::class, PermissionPolicy::class);
        Gate::policy(FeatureCategory::class, FeatureCategoryPolicy::class);
        Gate::policy(Feature::class, FeaturePolicy::class);
        Gate::policy(PropertyAttribute::class, PropertyAttributePolicy::class);
        Gate::policy(Property::class, PropertyPolicy::class);
        Gate::policy(PriceType::class, PriceTypePolicy::class);
    }
}
