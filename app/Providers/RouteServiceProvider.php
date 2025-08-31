<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Gate;

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

        $this->routes(function () {
            Route::middleware('api')
                ->prefix('api')
                ->group(base_path('routes/api.php'));

            // Bot routes (Telegram webhook and related endpoints)
            Route::middleware('api')
                ->group(base_path('routes/bot.php'));

            // Admin login routes (without auth)
            Route::middleware('web')
                ->prefix('admin')
                ->as('admin.')
                ->group(function () {
                    Route::get('/login', [\App\Http\Controllers\Admin\AdminLoginController::class, 'showLoginForm'])->name('login');
                    Route::post('/login', [\App\Http\Controllers\Admin\AdminLoginController::class, 'login']);
                    Route::post('/logout', [\App\Http\Controllers\Admin\AdminLoginController::class, 'logout'])->name('logout');
                });

            // Admin routes grouped by sections, loaded from routes/admin/*.php (with auth)
            Route::middleware(['web', 'admin.auth', 'admin.context'])
                ->prefix('admin')
                ->as('admin.')
                ->group(function () {
                    foreach (glob(base_path('routes/admin/*.php')) as $routeFile) {
                        require $routeFile;
                    }
                });

            Route::middleware('web')
                ->group(base_path('routes/web.php'));
        });
    }
}
