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
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

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
        // Собираем все параметры, включая '0' значения для фильтров
        $queryParams = $request->all();
        
        $filter = app()->make(UserFilter::class, ['queryParams' => $queryParams]);
        
        $query = $this->model::query();
        $filter->apply($query);
        
        // Загружаем связи и считаем количество для отображения
        $items = $query->with(['pets', 'orders', 'visits'])
            ->withCount(['pets', 'orders', 'visits'])
            ->paginate(25)
            ->appends($request->query());
        
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
        try {
            DB::beginTransaction();
            
            $validated = $request->validated();
            
            // Генерируем временный пароль
            $tempPassword = \Illuminate\Support\Str::random(8);
            $validated['password'] = Hash::make($tempPassword);
            
            $user = $this->model::create($validated);
            
            DB::commit();
            
            Log::info('Клиент успешно создан', [
                'user_id' => $user->id,
                'user_name' => $user->name,
                'user_email' => $user->email,
                'phone' => $user->phone ?? null
            ]);
            
            // TODO: Отправить временный пароль на email пользователя
            
            return redirect()
                ->route("admin.{$this->routePrefix}.index")
                ->with('success', 'Клиент успешно создан. Временный пароль: ' . $tempPassword);
                
        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Ошибка при создании клиента', [
                'data' => $request->validated(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return back()
                ->withInput()
                ->withErrors(['error' => 'Ошибка при создании клиента: ' . $e->getMessage()]);
        }
    }

    public function update(UpdateRequest $request, $id) : RedirectResponse
    {
        try {
            DB::beginTransaction();
            
            $validated = $request->validated();
            
            $item = $this->model::findOrFail($id);
            $oldName = $item->name;
            $oldEmail = $item->email;
            $oldPhone = $item->phone;
            
            $item->update($validated);
            
            DB::commit();
            
            Log::info('Данные клиента успешно обновлены', [
                'user_id' => $item->id,
                'old_name' => $oldName,
                'new_name' => $item->name,
                'old_email' => $oldEmail,
                'new_email' => $item->email,
                'old_phone' => $oldPhone,
                'new_phone' => $item->phone
            ]);
            
            return redirect()
                ->route("admin.{$this->routePrefix}.index")
                ->with('success', 'Данные клиента успешно обновлены');
                
        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Ошибка при обновлении клиента', [
                'user_id' => $id,
                'data' => $request->validated(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return back()
                ->withInput()
                ->withErrors(['error' => 'Ошибка при обновлении клиента: ' . $e->getMessage()]);
        }
    }

    public function resetPassword($id) : RedirectResponse
    {
        try {
            DB::beginTransaction();
            
            $user = $this->model::findOrFail($id);
            $tempPassword = \Illuminate\Support\Str::random(8);
            $user->update(['password' => Hash::make($tempPassword)]);
            
            DB::commit();
            
            Log::info('Пароль клиента успешно сброшен', [
                'user_id' => $id,
                'user_name' => $user->name,
                'user_email' => $user->email
            ]);
            
            return redirect()
                ->route("admin.{$this->routePrefix}.index")
                ->with('success', 'Пароль успешно сброшен. Новый временный пароль: ' . $tempPassword);
                
        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Ошибка при сбросе пароля клиента', [
                'user_id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return back()
                ->withErrors(['error' => 'Ошибка при сбросе пароля: ' . $e->getMessage()]);
        }
    }
} 