<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\AdminController;
use App\Http\Requests\Admin\Role\StoreRequest;
use App\Http\Requests\Admin\Role\UpdateRequest;
use App\Http\Filters\RoleSearchFilter;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\DB;

class RoleController extends AdminController
{
    protected $model;
    protected $viewPath;
    protected $routePrefix;
    protected $validationRules = [];
    protected $permissionPrefix;

    public function __construct()
    {
        parent::__construct();
        $this->model = Role::class;
        $this->viewPath = 'roles';
        $this->routePrefix = 'roles';
        $this->permissionPrefix = 'roles';
    }

    /**
     * Get module translations
     */
    private function getModuleTranslations(): array
    {
        return [
            'main' => 'Главная',
            'orders' => 'Заказы',
            'services' => 'Услуги',
            'statistics_general' => 'Общая статистика',
            'statistics_finance' => 'Финансовая статистика',
            'statistics_efficiency' => 'Статистика эффективности',
            'statistics_clients' => 'Статистика клиентов',
            'statistics_medicine' => 'Медицинская статистика',
            'statistics_conversion' => 'Статистика конверсии',
            'clients' => 'Клиенты',
            'pets' => 'Питомцы',
            'visits' => 'Приёмы',
            'vaccinations' => 'Вакцинации',
            'lab_tests' => 'Анализы',
            'drugs' => 'Препараты',
            'employees' => 'Сотрудники',
            'roles' => 'Роли',
            'schedules' => 'Расписания',
            'deliveries' => 'Поставки',
            'settings_analysis_types' => 'Типы анализов',
            'settings_analysis_parameters' => 'Параметры анализов',
            'settings_vaccination_types' => 'Типы вакцинаций',
            'settings_statuses' => 'Статусы',
            'settings_units' => 'Единицы измерения',
            'settings_branches' => 'Филиалы',
            'settings_specialties' => 'Специальности',
            'settings_animal_types' => 'Виды животных',
            'settings_breeds' => 'Породы животных',
            'settings_suppliers' => 'Поставщики',
            'settings_diagnoses' => 'Диагнозы (словарь)',
            'settings_symptoms' => 'Симптомы (словарь)',
        ];
    }

    /**
     * Get operation translations
     */
    private function getOperationTranslations(): array
    {
        return [
            'read' => 'Просмотр',
            'create' => 'Создание',
            'update' => 'Редактирование',
            'delete' => 'Удаление',
        ];
    }

    /**
     * Filter permissions for statistics modules (only read permission)
     */
    private function filterStatisticsPermissions($permissions)
    {
        $statisticsModules = [
            'statistics_general',
            'statistics_finance', 
            'statistics_efficiency',
            'statistics_clients',
            'statistics_medicine',
            'statistics_conversion'
        ];

        return $permissions->map(function ($modulePermissions, $module) use ($statisticsModules) {
            if (in_array($module, $statisticsModules)) {
                // Для модулей статистики оставляем только read права
                return $modulePermissions->filter(function ($permission) {
                    $parts = explode('.', $permission->name);
                    return ($parts[1] ?? '') === 'read';
                });
            }
            return $modulePermissions;
        });
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): View
    {
        $query = Role::where('guard_name', 'admin')
            ->with('permissions');

        // Применяем сортировку
        $sort = $request->get('sort', 'id_desc');
        switch ($sort) {
            case 'name_asc':
                $query->orderBy('name', 'asc');
                break;
            case 'name_desc':
                $query->orderBy('name', 'desc');
                break;
            case 'id_asc':
                $query->orderBy('id', 'asc');
                break;
            case 'id_desc':
            default:
                $query->orderBy('id', 'desc');
                break;
        }

        // Применяем поиск если есть параметр search
        if ($request->filled('search')) {
            $searchFilter = new RoleSearchFilter();
            $query = $searchFilter->apply($query, $request->search);
        }

        $roles = $query->get();

        $moduleTranslations = $this->getModuleTranslations();

        return view("admin.{$this->viewPath}.index", compact('roles', 'moduleTranslations'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        $permissions = Permission::where('guard_name', 'admin')
            ->get()
            ->sortBy(function ($permission) {
                $parts = explode('.', $permission->name);
                $module = $parts[0] ?? '';
                $operation = $parts[1] ?? '';
                
                // Порядок операций: read, create, update, delete
                $operationOrder = ['read' => 1, 'create' => 2, 'update' => 3, 'delete' => 4];
                $operationSort = $operationOrder[$operation] ?? 5;
                
                return $module . '.' . str_pad($operationSort, 2, '0', STR_PAD_LEFT);
            })
            ->groupBy(function ($permission) {
                $parts = explode('.', $permission->name);
                return $parts[0] ?? 'other';
            });

        // Фильтруем права для модулей статистики (только просмотр)
        $permissions = $this->filterStatisticsPermissions($permissions);

        $moduleTranslations = $this->getModuleTranslations();
        $operationTranslations = $this->getOperationTranslations();

        return view("admin.{$this->viewPath}.create", compact('permissions', 'moduleTranslations', 'operationTranslations'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreRequest $request): RedirectResponse
    {
        try {
            DB::beginTransaction();

            $role = Role::create([
                'name' => $request->name,
                'guard_name' => 'admin',
            ]);

            if ($request->has('permissions') && !empty($request->permissions)) {
                $permissionIds = $request->permissions;
                $permissions = Permission::whereIn('id', $permissionIds)->pluck('name')->toArray();
                $role->syncPermissions($permissions);
            }

            DB::commit();

            return redirect()
                ->route("admin.{$this->routePrefix}.index")
                ->with('success', 'Роль успешно создана');

        } catch (\Exception $e) {
            DB::rollBack();
            
            $errorMessage = 'Ошибка при создании роли';
            if (str_contains($e->getMessage(), 'permission named')) {
                $errorMessage = 'Ошибка при назначении прав. Попробуйте еще раз.';
            } elseif (str_contains($e->getMessage(), 'unique')) {
                $errorMessage = 'Роль с таким названием уже существует';
            }
            
            return redirect()
                ->back()
                ->withInput()
                ->with('error', $errorMessage);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($id): View
    {
        $role = Role::findOrFail($id);
        $role->load('permissions');
        
        $permissions = Permission::where('guard_name', 'admin')
            ->orderBy('name')
            ->get()
            ->groupBy(function ($permission) {
                $parts = explode('.', $permission->name);
                return $parts[0] ?? 'other';
            });

        $moduleTranslations = $this->getModuleTranslations();

        return view("admin.{$this->viewPath}.show", compact('role', 'permissions', 'moduleTranslations'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id): View
    {
        $role = Role::findOrFail($id);
        $role->load('permissions');
        
        $permissions = Permission::where('guard_name', 'admin')
            ->get()
            ->sortBy(function ($permission) {
                $parts = explode('.', $permission->name);
                $module = $parts[0] ?? '';
                $operation = $parts[1] ?? '';
                
                // Порядок операций: read, create, update, delete
                $operationOrder = ['read' => 1, 'create' => 2, 'update' => 3, 'delete' => 4];
                $operationSort = $operationOrder[$operation] ?? 5;
                
                return $module . '.' . str_pad($operationSort, 2, '0', STR_PAD_LEFT);
            })
            ->groupBy(function ($permission) {
                $parts = explode('.', $permission->name);
                return $parts[0] ?? 'other';
            });

        // Фильтруем права для модулей статистики (только просмотр)
        $permissions = $this->filterStatisticsPermissions($permissions);

        $moduleTranslations = $this->getModuleTranslations();
        $operationTranslations = $this->getOperationTranslations();

        return view("admin.{$this->viewPath}.edit", compact('role', 'permissions', 'moduleTranslations', 'operationTranslations'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateRequest $request, $id): RedirectResponse
    {
        try {
            DB::beginTransaction();

            $role = Role::findOrFail($id);
            $role->update([
                'name' => $request->name,
            ]);

            if ($request->has('permissions') && !empty($request->permissions)) {
                $permissionIds = $request->permissions;
                $permissions = Permission::whereIn('id', $permissionIds)->pluck('name')->toArray();
                $role->syncPermissions($permissions);
            } else {
                $role->syncPermissions([]);
            }

            DB::commit();

            return redirect()
                ->route("admin.{$this->routePrefix}.index")
                ->with('success', 'Роль успешно обновлена');

        } catch (\Exception $e) {
            DB::rollBack();
            
            $errorMessage = 'Ошибка при обновлении роли';
            if (str_contains($e->getMessage(), 'permission named')) {
                $errorMessage = 'Ошибка при назначении прав. Попробуйте еще раз.';
            } elseif (str_contains($e->getMessage(), 'unique')) {
                $errorMessage = 'Роль с таким названием уже существует';
            }
            
            return redirect()
                ->back()
                ->withInput()
                ->with('error', $errorMessage);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id): RedirectResponse
    {
        try {
            $role = Role::findOrFail($id);
            
            // Проверяем, что роль не является системной
            if (in_array($role->name, ['super-admin', 'admin'])) {
                return redirect()
                    ->back()
                    ->with('error', 'Нельзя удалить системную роль');
            }

            // Проверяем, есть ли пользователи с этой ролью
            $usersWithRole = DB::table('model_has_roles')
                ->where('role_id', $role->id)
                ->where('model_type', 'App\Models\Employee')
                ->count();

            if ($usersWithRole > 0) {
                return redirect()
                    ->back()
                    ->with('error', 'Нельзя удалить роль, которая назначена пользователям');
            }

            $role->delete();

            return redirect()
                ->route("admin.{$this->routePrefix}.index")
                ->with('success', 'Роль успешно удалена');

        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'Ошибка при удалении роли: ' . $e->getMessage());
        }
    }

    /**
     * Get roles options for tomselect
     */
    public function options(Request $request)
    {
        $query = $request->get('q', '');
        $selected = $request->get('selected', '');
        
        $roles = Role::where('guard_name', 'admin')
            ->when($query, function ($q) use ($query) {
                $q->where('name', 'like', "%{$query}%");
            })
            ->orderBy('name')
            ->limit(20)
            ->get();

        $options = $roles->map(function ($role) {
            return [
                'value' => $role->id,
                'text' => $role->name,
            ];
        });

        // Если указан selected, добавляем его в результат
        if ($selected) {
            $selectedRole = Role::where('guard_name', 'admin')
                ->where('id', $selected)
                ->first();
            
            if ($selectedRole && !$options->contains('value', $selected)) {
                $options->prepend([
                    'value' => $selectedRole->id,
                    'text' => $selectedRole->name,
                ]);
            }
        }

        return response()->json($options);
    }
}
