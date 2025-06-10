<?php

namespace App\Http\Controllers\Admin;

use App\Models\User;
use App\Http\Filters\UserFilter;
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
        $this->validationRules = [
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email',
            'phone' => 'required|string|max:20',
            'address' => 'nullable|string|max:255',
        ];
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

    public function show($id) : View
    {
        $user = $this->model::with(['pets', 'appointments'])->findOrFail($id);
        return view("admin.{$this->viewPath}.show", compact('user'));
    }

    public function store(Request $request) : RedirectResponse
    {
        $validated = $request->validate($this->validationRules);
        
        // Генерируем временный пароль
        $tempPassword = \Illuminate\Support\Str::random(8);
        $validated['password'] = Hash::make($tempPassword);
        
        $user = $this->model::create($validated);
        
        // TODO: Отправить временный пароль на email пользователя
        
        return redirect()
            ->route("admin.{$this->routePrefix}.index")
            ->with('success', 'Клиент успешно создан. Временный пароль: ' . $tempPassword);
    }

    public function update(Request $request, $id) : RedirectResponse
    {
        $validationRules = $this->validationRules;
        $validationRules['email'] = 'required|email|max:255|unique:users,email,' . $id;
        
        $validated = $request->validate($validationRules);
        
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