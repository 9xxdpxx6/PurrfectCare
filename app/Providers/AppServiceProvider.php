<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Log;
use Monolog\Logger;

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
        Paginator::defaultView('vendor.pagination.custom');
        
        // Устанавливаем английскую локаль для логирования
        setlocale(LC_TIME, 'en_US.UTF-8', 'en_US', 'en');
        
        // Принудительно устанавливаем английский для Monolog
        if (class_exists('Monolog\Logger')) {
            // Убираем неправильный вызов статического метода
            // Logger::setTimezone(new \DateTimeZone('UTC'));
        }
    }
}
