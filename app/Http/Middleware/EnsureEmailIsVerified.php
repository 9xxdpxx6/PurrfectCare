<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureEmailIsVerified
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check() && !Auth::user()->hasVerifiedEmail()) {
            // Если пользователь авторизован, но email не подтвержден
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Необходимо подтвердить email для доступа к этой функции.',
                    'verified' => false
                ], 403);
            }
            
            return redirect()->route('client.verify-email')
                ->with('error', 'Необходимо подтвердить email для записи на прием.');
        }

        return $next($request);
    }
}
