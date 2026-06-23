<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        $this->configureRateLimiting();
    }

    /**
     * Rate limiting rules - adapted from Taurus CRM RouteServiceProvider.
     */
    protected function configureRateLimiting(): void
    {
        // Login: 10 attempts per minute per IP - brute-force protection
        RateLimiter::for('login', function (Request $request) {
            return Limit::perMinute(10)
                ->by($request->ip())
                ->response(fn() => redirect()->back()
                    ->withErrors(['email' => 'Too many login attempts. Please wait and try again.'])
                );
        });

        // General web: 300 req/min per user or IP - DDoS / flood protection
        RateLimiter::for('web', function (Request $request) {
            return Limit::perMinute(300)
                ->by(optional($request->user())->id ?: $request->ip());
        });

        // API: 60 req/min
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)
                ->by(optional($request->user())->id ?: $request->ip());
        });

        // Booking form submissions: 30/min per user (prevent spam creation)
        RateLimiter::for('bookings', function (Request $request) {
            return Limit::perMinute(30)
                ->by(optional($request->user())->id ?: $request->ip());
        });
    }
}
