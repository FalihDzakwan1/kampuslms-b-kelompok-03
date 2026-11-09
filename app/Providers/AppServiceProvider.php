<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;
use App\Policies\NotificationPolicy;

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
        // Paksa skema HTTPS di lingkungan produksi atau saat di balik reverse proxy SSL (seperti Railway)
        if ($this->app->environment('production') || request()->header('x-forwarded-proto') === 'https' || str_contains((string) config('app.url'), 'https://')) {
            URL::forceScheme('https');
            request()->server->set('HTTPS', 'on');
        }

        // [ACCPT-PERF-LAZY-LOADING] Cegah N+1 query / lazy loading di lingkungan non-produksi
        Model::preventLazyLoading(! $this->app->isProduction());

        // Daftarkan policy untuk notifikasi database
        Gate::policy(DatabaseNotification::class, NotificationPolicy::class);

        // Rate limiter umum API: 60 request per menit per user ID atau IP
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });

        // Rate limiter login: 5 request per menit per email dan IP
        RateLimiter::for('login', function (Request $request) {
            return Limit::perMinute(5)->by($request->input('email') . '|' . $request->ip());
        });
    }
}
