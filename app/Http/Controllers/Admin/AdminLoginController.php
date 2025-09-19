<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class AdminLoginController extends Controller {
    public function showLoginForm() {
        if (Auth::guard('admin')->check()) {
            return redirect('/admin/dashboard');
        }
        return view('admin.login');
    }

    public function login(Request $request) {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $credentials = $request->only('email', 'password');
        $remember = $request->boolean('remember');

        if (Auth::guard('admin')->attempt($credentials, $remember)) {
            // Проверяем, что пользователь имеет необходимые роли
            $employee = Auth::guard('admin')->user();
            
            if (!$employee || !$employee->roles()->where('guard_name', 'admin')->exists()) {
                // Если нет прав - делаем logout и показываем ошибку
                Auth::guard('admin')->logout();
                throw ValidationException::withMessages([
                    'email' => ['У вас нет полномочий для доступа к админ-панели.'],
                ]);
            }
            
            // Проверяем, что сотрудник активен
            if (!$employee->is_active) {
                Auth::guard('admin')->logout();
                throw ValidationException::withMessages([
                    'email' => ['Ваш аккаунт деактивирован. Обратитесь к администратору.'],
                ]);
            }
            
            // Обновляем время последнего входа
            $employee->update(['last_login_at' => now()]);
            
            $request->session()->regenerate();
            return redirect()->intended('/admin/dashboard');
        }

        throw ValidationException::withMessages([
            'email' => ['Неверный логин или пароль.'],
        ]);
    }

    public function logout(Request $request) {
        Auth::guard('admin')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        
        return redirect('/admin/login');
    }
}
