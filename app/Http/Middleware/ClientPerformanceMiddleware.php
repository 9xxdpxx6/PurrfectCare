<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class ClientPerformanceMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        $startTime = microtime(true);
        
        // Кэширование статических данных
        if ($request->is('about') || $request->is('contacts')) {
            $cacheKey = 'client_' . $request->path();
            $cachedResponse = Cache::get($cacheKey);
            
            if ($cachedResponse) {
                return response($cachedResponse);
            }
        }
        
        $response = $next($request);
        
        // Логирование производительности
        $executionTime = microtime(true) - $startTime;
        
        if ($executionTime > 1.0) { // Логируем медленные запросы
            Log::warning('Slow client request', [
                'url' => $request->fullUrl(),
                'method' => $request->method(),
                'execution_time' => $executionTime,
                'memory_usage' => memory_get_usage(true),
                'peak_memory' => memory_get_peak_usage(true)
            ]);
        }
        
        // Кэширование ответа для статических страниц
        if ($request->is('about') || $request->is('contacts')) {
            $cacheKey = 'client_' . $request->path();
            Cache::put($cacheKey, $response->getContent(), 3600); // 1 час
        }
        
        return $response;
    }
}
