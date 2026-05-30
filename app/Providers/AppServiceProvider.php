<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL; // <-- Pastikan baris ini di-import

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
        // Paksa Laravel dan Vite menggunakan HTTPS jika berjalan di Railway
        if (env('RAILWAY_ENVIRONMENT') || config('app.env') === 'production') {
            URL::forceScheme('https');
        }

        \Illuminate\Support\Facades\RateLimiter::for('api-upload', function (\Illuminate\Http\Request $request) {
            return \Illuminate\Cache\RateLimiting\Limit::perMinute(config('security.rate_limits.api_upload', 60))->by($request->user()?->id ?: $request->ip());
        });

        \Illuminate\Support\Facades\RateLimiter::for('api-editor', function (\Illuminate\Http\Request $request) {
            return \Illuminate\Cache\RateLimiting\Limit::perMinute(config('security.rate_limits.api_editor', 120))->by($request->user()?->id ?: $request->ip());
        });
    }
}