<?php

namespace App\Http\Controllers\Admin;

use App\Models\User;
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
            'notes' => 'nullable|string',
            'is_active' => 'required|boolean'
        ];
    }

    public function index() : View
    {
        $items = $this->model::with('pets')->paginate(10);
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
        $validated['is_active'] = $request->has('is_active');
        
        // Генерируем временный пароль
        $tempPassword = str_random(8);
        $validated['password'] = Hash::make($tempPassword);
        
        $user = $this->model::create($validated);
        
        // TODO: Отправить временный пароль на email пользователя
        
        return redirect()
            ->route("admin.{$this->routePrefix}.index")
            ->with('success', 'Пользователь успешно создан. Временный пароль: ' . $tempPassword);
    }

    public function update(Request $request, $id) : RedirectResponse
    {
        $validated = $request->validate($this->validationRules);
        $validated['is_active'] = $request->has('is_active');
        
        $item = $this->model::findOrFail($id);
        $item->update($validated);
        
        return redirect()
            ->route("admin.{$this->routePrefix}.index")
            ->with('success', 'Данные пользователя успешно обновлены');
    }

    public function resetPassword($id) : RedirectResponse
    {
        $user = $this->model::findOrFail($id);
        $tempPassword = str_random(8);
        $user->update(['password' => Hash::make($tempPassword)]);
        
        // TODO: Отправить новый временный пароль на email пользователя
        
        return redirect()
            ->route("admin.{$this->routePrefix}.index")
            ->with('success', 'Пароль успешно сброшен. Новый временный пароль: ' . $tempPassword);
    }
} 