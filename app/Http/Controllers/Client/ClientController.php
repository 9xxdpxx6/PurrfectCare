<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\Employee;
use App\Models\User;
use App\Services\VeterinarianService;
use App\Notifications\ClientRegistrationNotification;
use App\Http\Requests\Client\LoginRequest;
use App\Http\Requests\Client\RegisterRequest;
use App\Http\Requests\Client\Profile\UpdateRequest as UpdateProfileRequest;
use App\Http\Requests\Client\ChangePasswordRequest;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class ClientController extends Controller
{
    protected $veterinarianService;

    public function __construct(VeterinarianService $veterinarianService)
    {
        $this->veterinarianService = $veterinarianService;
    }

    /**
     * Главная страница клиентской части
     */
    public function index(): View
    {
        // Получаем статистику с сервера
        $stats = $this->getStatistics();
        
        return view('client.index', compact('stats'));
    }

    /**
     * Получение статистики для главной страницы
     */
    private function getStatistics(): array
    {
        // Реалистичные минимумы
        $minSatisfiedClients = 150;
        $minYearsExperience = 3;
        $minTreatedPets = 50;
        $minServicesCount = 10;
        
        // Получаем реальные данные
        $satisfiedClients = User::role('client', 'web')->count();
        $yearsExperience = $this->getYearsExperience();
        $treatedPets = $this->getTreatedPets();
        $servicesCount = $this->getServicesCount();
        
        return [
            'satisfied_clients' => $satisfiedClients,
            'years_experience' => $yearsExperience,
            'treated_pets' => $treatedPets,
            'services_count' => $servicesCount,
        ];
    }

    /**
     * Получение лет опыта (считается с 1 сентября 2018 года)
     */
    private function getYearsExperience(): int
    {
        // Дата основания клиники
        $foundingDate = \Carbon\Carbon::create(2018, 9, 1);
        $now = \Carbon\Carbon::now();
        
        // Считаем разность в годах
        $yearsExperience = $foundingDate->diffInYears($now);
        
        return $yearsExperience;
    }

    /**
     * Получение количества вылеченных питомцев
     */
    private function getTreatedPets(): int
    {
        // Считаем количество уникальных питомцев, которые посещали клинику
        $treatedPets = \App\Models\Pet::whereHas('visits')->count();
        return $treatedPets;
    }

    /**
     * Получение количества ветеринаров
     */
    private function getVeterinariansCount(): int
    {
        // Считаем количество активных ветеринаров
        $veterinarians = \App\Models\Employee::role('veterinarian', 'admin')
            ->where('is_active', true)
            ->count();
        return $veterinarians;
    }

    /**
     * Получение количества услуг
     */
    private function getServicesCount(): int
    {
        // Считаем количество активных услуг
        $servicesCount = \App\Models\Service::count();
        return $servicesCount;
    }

    /**
     * Получение количества филиалов
     */
    private function getBranchesCount(): int
    {
        // Считаем количество активных филиалов
        $branchesCount = \App\Models\Branch::count();
        return $branchesCount;
    }

    /**
     * Страница входа
     */
    public function login(): View
    {
        return view('client.auth.login');
    }

    /**
     * Страница регистрации
     */
    public function register(): View
    {
        return view('client.auth.register');
    }

    /**
     * Обработка входа
     */
    public function authenticate(LoginRequest $request)
    {

        if (Auth::attempt($request->only('email', 'password'))) {
            $request->session()->regenerate();
            return redirect()->intended('/');
        }

        return back()->withErrors([
            'email' => 'Неверный логин или пароль.',
        ])->onlyInput('email');
    }

    /**
     * Обработка регистрации
     */
    public function store(RegisterRequest $request)
    {

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'password' => Hash::make($request->password),
        ]);

        Auth::login($user);

        // Отправляем письмо подтверждения email
        $user->sendEmailVerificationNotification();

        // Отправляем уведомление о регистрации
        $user->notify(new ClientRegistrationNotification($user));

        return redirect()->route('client.verify-email')
            ->with('success', 'Регистрация прошла успешно! Проверьте email для подтверждения адреса.');
    }

    /**
     * Выход
     */
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/');
    }

    /**
     * Личный кабинет - профиль
     */
    public function profile(): View
    {
        $user = Auth::user();
        return view('client.profile.index', compact('user'));
    }

    /**
     * Обновление профиля
     */
    public function updateProfile(UpdateProfileRequest $request)
    {

        $user = Auth::user();
        $user->update($request->only(['name', 'email', 'phone', 'address']));

        return back()->with('success', 'Профиль успешно обновлен!');
    }

    /**
     * Смена пароля
     */
    public function changePassword(ChangePasswordRequest $request)
    {
        $user = Auth::user();

        $user->update([
            'password' => Hash::make($request->password)
        ]);

        return back()->with('success', 'Пароль успешно изменен!');
    }

    /**
     * Страница "О нас"
     */
    public function about(): View
    {
        // Получаем информацию о команде через сервис
        $veterinarians = $this->veterinarianService->getAllVeterinarians();
        
        // Функция для честного округления чисел
        $roundNumber = function($number) {
            if ($number <= 20) {
                return round($number / 5) * 5;
            } elseif ($number <= 100) {
                return round($number / 10) * 10;
            } else {
                return round($number / 50) * 50;
            }
        };
        
        // Статистика для страницы "О нас" с красивым округлением
        $rawStats = [
            'employees_count' => \App\Models\Employee::count(),
            'pets_count' => \App\Models\Pet::count(),
            'visits_count' => \App\Models\Visit::count(),
            'orders_count' => \App\Models\Order::count(),
            'services_count' => \App\Models\Service::count(),
            'branches_count' => \App\Models\Branch::count(),
            'clients_count' => \App\Models\User::count(),
        ];
        
        $stats = array_map($roundNumber, $rawStats);

        return view('client.about', compact('veterinarians', 'stats'));
    }

    /**
     * Страница "Контакты"
     */
    public function contacts(): View
    {
        // Получаем все филиалы с их данными
        $branches = Branch::distinct()->get();

        return view('client.contacts', compact('branches'));
    }

    /**
     * Страница "Политика конфиденциальности"
     */
    public function privacy(): View
    {
        return view('client.privacy');
    }

    /**
     * Страница "Условия использования"
     */
    public function terms(): View
    {
        return view('client.terms');
    }
}
