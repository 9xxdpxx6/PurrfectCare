<?php

namespace App\Http\Controllers\Admin;

use App\Models\User;
use App\Http\Filters\UserFilter;
use App\Http\Requests\Admin\User\StoreRequest;
use App\Http\Requests\Admin\User\UpdateRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class UserController extends AdminController
{
    public function __construct()
    {
        $this->model = User::class;
        $this->viewPath = 'users';
        $this->routePrefix = 'users';
    }

    public function index(Request $request) : View
    {
        $filter = app()->make(UserFilter::class, ['queryParams' => array_filter($request->all())]);
        
        $query = $this->model::with(['pets', 'orders', 'visits']);
        $filter->apply($query);
        
        // Подсчитаем статистику для каждого пользователя
        $items = $query->paginate(25)->appends($request->query());
        
        foreach ($items as $user) {
            $user->pets_count = $user->pets->count();
            $user->orders_count = $user->orders->count();
            $user->visits_count = $user->visits->count();
        }
        
        return view("admin.{$this->viewPath}.index", compact('items'));
    }

    public function edit($id): View
    {
        $item = $this->model::findOrFail($id);
        return view("admin.{$this->viewPath}.edit", compact('item'));
    }

    public function show($id) : View
    {
        $user = $this->model::findOrFail($id);
        
        // Получаем общее количество записей
        $petsTotal = $user->pets()->count();
        $ordersTotal = $user->orders()->count();
        $visitsTotal = $user->visits()->count();
        
        // Загружаем ограиченные данные для отображения
        $pets = $user->pets()->with(['breed.species'])->latest()->limit(10)->get();
        $orders = $user->orders()->with(['pet'])->latest()->limit(10)->get();
        $visits = $user->visits()->with(['pet', 'schedule.veterinarian'])->latest()->limit(10)->get();
        
        return view("admin.{$this->viewPath}.show", compact('user', 'pets', 'orders', 'visits', 'petsTotal', 'ordersTotal', 'visitsTotal'));
    }

    public function store(StoreRequest $request) : RedirectResponse
    {
        $validated = $request->validated();
        
        // Генерируем временный пароль
        $tempPassword = \Illuminate\Support\Str::random(8);
        $validated['password'] = Hash::make($tempPassword);
        
        $user = $this->model::create($validated);
        
        // TODO: Отправить временный пароль на email пользователя
        
        return redirect()
            ->route("admin.{$this->routePrefix}.index")
            ->with('success', 'Клиент успешно создан. Временный пароль: ' . $tempPassword);
    }

    public function update(UpdateRequest $request, $id) : RedirectResponse
    {
        $validated = $request->validated();
        
        $item = $this->model::findOrFail($id);
        $item->update($validated);
        
        return redirect()
            ->route("admin.{$this->routePrefix}.index")
            ->with('success', 'Данные клиента успешно обновлены');
    }

    public function resetPassword($id) : RedirectResponse
    {
        $user = $this->model::findOrFail($id);
        $tempPassword = \Illuminate\Support\Str::random(8);
        $user->update(['password' => Hash::make($tempPassword)]);
        
        return redirect()
            ->route("admin.{$this->routePrefix}.index")
            ->with('success', 'Пароль успешно сброшен. Новый временный пароль: ' . $tempPassword);
    }
} 