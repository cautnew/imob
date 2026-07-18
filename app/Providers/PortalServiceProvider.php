<?php

namespace App\Providers;

use Illuminate\Auth\Middleware\RedirectIfAuthenticated;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;

class PortalServiceProvider extends ServiceProvider
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
        $this->configureRedirects();
        $this->configureRateLimiting();
    }

    /**
     * Redirect an already-authenticated principal to the right dashboard,
     * branching on the guard actually authenticated for this request.
     */
    private function configureRedirects(): void
    {
        RedirectIfAuthenticated::redirectUsing(fn () => Auth::guard('lessee')->check()
            ? route('portal.dashboard')
            : route('dashboard'));
    }

    /**
     * Configure rate limiting for the portal's login endpoint.
     */
    private function configureRateLimiting(): void
    {
        RateLimiter::for('lessee-login', function (Request $request) {
            $throttleKey = Str::transliterate(Str::lower((string) $request->input('document')).'|'.$request->ip());

            return Limit::perMinute(5)->by($throttleKey);
        });
    }
}
