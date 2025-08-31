<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class SetAdminAuthContext
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Устанавливаем admin guard как текущий для Gate проверок
        $adminUser = Auth::guard('admin')->user();
        
        if ($adminUser) {
            // Временно переопределяем auth()->user() для этого запроса
            app('auth')->setUser($adminUser);
            
            // Также устанавливаем для Gate
            Gate::forUser($adminUser);
        }

        return $next($request);
    }
}
