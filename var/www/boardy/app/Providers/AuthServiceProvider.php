<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Laravel\Passport\Passport;

class AuthServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        Passport::authorizationView('auth.oauth.authorize');
        Passport::tokensExpireIn(now()->addMinutes((int) env('PASSPORT_ACCESS_TOKEN_MINUTES', 15)));
        Passport::refreshTokensExpireIn(now()->addDays(30));
    }
}
