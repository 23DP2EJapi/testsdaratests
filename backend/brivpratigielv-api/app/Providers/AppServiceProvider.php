<?php

namespace App\Providers;

use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Vite;
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
        Vite::prefetch(concurrency: 3);
        if (config('app.env') === 'production') {
            \URL::forceScheme('https');
        }

        VerifyEmail::createUrlUsing(function ($notifiable) {
            $signedUrl = URL::temporarySignedRoute(
                'api.verification.verify',
                Carbon::now()->addMinutes(60),
                ['id' => $notifiable->getKey(), 'hash' => sha1($notifiable->getEmailForVerification())]
            );

            $frontendUrl = env('FRONTEND_URL', 'https://brivpratigie.up.railway.app');
            return $frontendUrl . '/verify-email?verify_url=' . urlencode($signedUrl);
        });
    }
}
