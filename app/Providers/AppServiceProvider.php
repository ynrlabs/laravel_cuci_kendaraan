<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

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
        Model::shouldBeStrict(!app()->isProduction());

        // Login rate limiter: Max 5 attempts per minute per email + IP
        RateLimiter::for('login', function (Request $request) {
            $throttleKey = strtolower((string) $request->input('email')).'|'.$request->ip();

            return Limit::perMinute(5)->by($throttleKey)->response(function () {
                return back()->withErrors([
                    'email' => 'Terlalu banyak percobaan login yang gagal. Silakan coba lagi dalam 1 menit.',
                ])->onlyInput('email');
            });
        });

        // OTP rate limiter: Max 6 requests per minute
        RateLimiter::for('otp', function (Request $request) {
            $phone = (string) $request->input('phone', $request->ip());

            return Limit::perMinute(6)->by($phone);
        });

        // General API rate limiter: Max 60 requests per minute per user / IP
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });
    }
}

