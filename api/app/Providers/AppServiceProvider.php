<?php

namespace App\Providers;

use App\Models\PersonalAccessToken;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Routing\UrlGenerator;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Laravel\Sanctum\Sanctum;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(UrlGenerator $url): void
    {
        if ($this->app->environment('production')) {
            $url->forceScheme('https');
        }

        Sanctum::usePersonalAccessTokenModel(PersonalAccessToken::class);

        RateLimiter::for('login', function (Request $request) {
            $email = $request->input('email');
            $emailKey = is_string($email) ? $email : 'invalid';

            return Limit::perMinute(5)->by($request->ip().'|'.$emailKey);
        });

        RateLimiter::for('exports', function (Request $request) {
            return Limit::perMinute(10)->by((string) $request->user()?->getKey() ?: $request->ip());
        });
    }
}
