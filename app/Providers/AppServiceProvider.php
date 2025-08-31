<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Auth;

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
                            // Настройка Spatie Permissions для работы с разными guard'ами
                    Gate::before(function ($user, $ability) {
                        // Если пользователь аутентифицирован через admin guard
                        if (Auth::guard('admin')->check()) {
                            $adminUser = Auth::guard('admin')->user();
                            if ($adminUser && $adminUser->hasRole('super-admin')) {
                                return true;
                            }
                        }
                    });
    }
}
