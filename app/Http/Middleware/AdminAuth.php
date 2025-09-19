<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class AdminAuth
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!Auth::guard('admin')->check()) {
            return redirect('/admin/login');
        }

        // Проверяем, что пользователь имеет хотя бы одну роль с guard admin
        $user = Auth::guard('admin')->user();
        if (!$user || !$user->roles()->where('guard_name', 'admin')->exists()) {
            Auth::guard('admin')->logout();
            return redirect('/admin/login')->with('error', 'У вас нет доступа к админ-панели');
        }

        // Проверяем, что сотрудник активен
        if (!$user->is_active) {
            Auth::guard('admin')->logout();
            return redirect('/admin/login')->with('error', 'Ваш аккаунт деактивирован. Обратитесь к администратору.');
        }

        return $next($request);
    }
}
