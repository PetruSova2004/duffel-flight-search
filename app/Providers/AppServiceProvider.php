<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

class AppServiceProvider extends ServiceProvider
{
    /**
     * @return void
     */
    public function register(): void
    {
        //
    }

    /**
     * @return void
     */
    public function boot(): void
    {
        Password::defaults(fn () => Password::min(8)->letters()->numbers());

        $this->configureRateLimiting();
    }

    /**
     * @return void
     */
    private function configureRateLimiting(): void
    {
        // Per-IP cap on top of the per e-mail limit in LoginRequest (credential stuffing)
        RateLimiter::for('login', fn (Request $request) => Limit::perMinute(20)->by($request->ip()));

        RateLimiter::for('register', fn (Request $request) => Limit::perMinute(5)->by($request->ip()));

        // Each search is a paid-for call to airlines on Duffel's side
        RateLimiter::for('flight-search', fn (Request $request) => Limit::perMinute(10)->by($request->user()?->id ?: $request->ip()));
    }
}
