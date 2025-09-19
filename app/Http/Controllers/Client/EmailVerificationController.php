<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Carbon;

class EmailVerificationController extends Controller
{
    /**
     * Показать страницу с информацией о необходимости подтверждения email
     */
    public function show()
    {
        $user = Auth::user();
        
        if ($user->hasVerifiedEmail()) {
            return redirect()->route('client.index');
        }
        
        return view('client.auth.verify-email', compact('user'));
    }

    /**
     * Отправить повторное письмо для подтверждения email
     */
    public function resend(Request $request): RedirectResponse
    {
        $user = Auth::user();
        
        if ($user->hasVerifiedEmail()) {
            return redirect()->route('client.index');
        }
        
        // Отправляем письмо подтверждения
        $user->sendEmailVerificationNotification();
        
        return back()->with('success', 'Письмо с подтверждением отправлено повторно!');
    }

    /**
     * Подтвердить email по ссылке
     */
    public function verify(Request $request): RedirectResponse
    {
        $user = User::findOrFail($request->route('id'));
        
        // Проверяем подпись URL
        if (!URL::hasValidSignature($request)) {
            return redirect()->route('client.verify-email')
                ->with('error', 'Недействительная ссылка подтверждения.');
        }
        
        // Проверяем, что email еще не подтвержден
        if ($user->hasVerifiedEmail()) {
            return redirect()->route('client.index')
                ->with('success', 'Email уже подтвержден!');
        }
        
        // Подтверждаем email
        $user->markEmailAsVerified();
        
        // Авторизуем пользователя, если он не авторизован
        if (!Auth::check()) {
            Auth::login($user);
        }
        
        return redirect()->route('client.index')
            ->with('success', 'Email успешно подтвержден! Теперь вы можете записываться на прием.');
    }

    /**
     * Показать страницу успешного подтверждения
     */
    public function verified(): View
    {
        return view('client.auth.email-verified');
    }
}
