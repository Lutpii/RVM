<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * The path to your application's "home" route.
     *
     * Typically, users are redirected here after authentication.
     *
     * @var string
     */
    public const HOME = '/home';

    /**
     * Define your route model bindings, pattern filters, and other route configuration.
     */
    public function boot(): void
    {
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });

        // /qr/status/{token} is public (the physical kiosk polls it with no auth,
        // by design) and its {token} space is small enough to brute-force under
        // the generic 60/min API limit, so it gets its own tighter cap. A real
        // kiosk only ever polls its own single token every few seconds — this
        // limit only bites a sweep across many guessed tokens.
        RateLimiter::for('qr-status', function (Request $request) {
            return Limit::perMinute(20)->by($request->ip());
        });

        // The admin Detection Review tab is inherently request-bursty: opening
        // one page of the gallery fires 1 list request plus one authenticated
        // image fetch per row (up to 200 with the largest page size), and each
        // Correct/Incorrect click adds a PATCH. Reviewing a couple of pages a
        // minute blows straight through the generic 60/min 'api' limit, and the
        // failure is silent and misleading (a 429'd thumbnail just renders as a
        // "photo missing" placeholder). These routes all sit behind
        // admin + auth:sanctum, so keying by user id is safe and a generous cap
        // costs nothing — only an authenticated admin can reach them at all.
        RateLimiter::for('detection-review', function (Request $request) {
            return Limit::perMinute(600)->by($request->user()?->id);
        });

        $this->routes(function () {
            Route::middleware('api')
                ->prefix('api')
                ->group(base_path('routes/api.php'));

            Route::middleware('web')
                ->group(base_path('routes/web.php'));
        });
    }
}
