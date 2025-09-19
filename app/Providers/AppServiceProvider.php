<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Pagination\Paginator;

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
        // Настройка глобальной пагинации
        Paginator::defaultView('vendor.pagination.custom');
        
        // Настройка HTTP клиента для Telegram API
        Http::macro('telegram', function () {
            $options = [
                'timeout' => 10,
            ];
            
            // Отключаем проверку SSL только в локальной разработке
            if (app()->environment('local', 'testing')) {
                $options['verify'] = false;
            }
            
            return Http::withOptions($options);
        });
        
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
