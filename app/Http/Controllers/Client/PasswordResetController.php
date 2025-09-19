<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Http\Requests\Client\PasswordReset\ForgotPasswordRequest;
use App\Http\Requests\Client\PasswordReset\ResetPasswordRequest;
use App\Mail\ClientPasswordResetNotification;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\View\View;

class PasswordResetController extends Controller
{
    /**
     * Показать форму запроса сброса пароля
     */
    public function showForgotPasswordForm(): View
    {
        return view('client.auth.forgot-password');
    }

    /**
     * Обработать запрос сброса пароля
     */
    public function sendResetLinkEmail(ForgotPasswordRequest $request)
    {
        $email = $request->validated()['email'];

        // Проверяем, существует ли пользователь с таким email
        $user = User::where('email', $email)->first();

        // Всегда показываем одинаковое сообщение для безопасности
        // Это предотвращает атаки на перечисление пользователей
        if ($user) {
            // Генерируем токен сброса пароля
            $token = Str::random(64);
            
            // Сохраняем токен в базе данных
            DB::table('password_reset_tokens')->updateOrInsert(
                ['email' => $email],
                [
                    'token' => Hash::make($token),
                    'created_at' => now()
                ]
            );

            // Отправляем письмо с ссылкой для сброса пароля
            try {
                Mail::to($email)->send(new ClientPasswordResetNotification($user, $token));
            } catch (\Exception $e) {
                // Логируем ошибку, но не раскрываем информацию пользователю
                \Log::error('Ошибка отправки письма сброса пароля', [
                    'email' => $email,
                    'error' => $e->getMessage()
                ]);
            }
        }

        // Всегда показываем одинаковое сообщение
        return back()->with('success', 'Письмо с инструкциями по сбросу пароля отправлено на указанный email.');
    }

    /**
     * Показать форму ввода нового пароля
     */
    public function showResetForm(Request $request): View
    {
        $token = $request->route('token');
        $email = $request->query('email');

        return view('client.auth.reset-password', compact('token', 'email'));
    }

    /**
     * Обработать сброс пароля
     */
    public function reset(ResetPasswordRequest $request)
    {
        $validated = $request->validated();
        $email = $validated['email'];
        $token = $validated['token'];
        $password = $validated['password'];

        // Проверяем токен
        $passwordReset = DB::table('password_reset_tokens')
            ->where('email', $email)
            ->first();

        if (!$passwordReset || !Hash::check($token, $passwordReset->token)) {
            return back()->withErrors(['token' => 'Неверный или истекший токен сброса пароля.']);
        }

        // Проверяем, не истек ли токен (24 часа)
        if (now()->diffInHours($passwordReset->created_at) > 24) {
            DB::table('password_reset_tokens')->where('email', $email)->delete();
            return back()->withErrors(['token' => 'Токен сброса пароля истек. Запросите новый.']);
        }

        // Обновляем пароль пользователя
        $user = User::where('email', $email)->first();
        if (!$user) {
            return back()->withErrors(['email' => 'Пользователь не найден.']);
        }

        $user->update([
            'password' => Hash::make($password)
        ]);

        // Удаляем использованный токен
        DB::table('password_reset_tokens')->where('email', $email)->delete();

        return redirect()->route('client.login')
            ->with('success', 'Пароль успешно изменен. Теперь вы можете войти в систему.');
    }
}
