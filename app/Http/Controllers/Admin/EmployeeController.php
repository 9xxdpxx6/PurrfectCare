<?php

namespace App\Http\Controllers\Admin;

use App\Models\Employee;
use App\Models\Specialty;
use App\Models\Branch;
use App\Models\Order;
use App\Models\Vaccination;
use App\Models\LabTest;
use App\Models\Schedule;
use Spatie\Permission\Models\Role;
use App\Http\Requests\Admin\Employee\StoreRequest;
use App\Http\Requests\Admin\Employee\UpdateRequest;
use App\Http\Filters\EmployeeFilter;
use App\Http\Traits\HasOptionsMethods;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use App\Mail\EmployeePasswordReset;

class EmployeeController extends AdminController
{
    use HasOptionsMethods;

    public function __construct()
    {
        parent::__construct();
        $this->model = Employee::class;
        $this->viewPath = 'employees';
        $this->routePrefix = 'employees';
        $this->permissionPrefix = 'employees';
    }

        public function index(Request $request) : View
    {
        $filter = app(EmployeeFilter::class, ['queryParams' => array_filter($request->all())]);
        $employees = Employee::filter($filter)
            ->with(['specialties', 'branches', 'roles'])
            ->paginate(12)
            ->withQueryString();
        $specialties = Specialty::orderBy('name')->get();
        $branches = Branch::orderBy('name')->get();
        return view('admin.employees.index', compact('employees', 'specialties', 'branches'));
    }

    public function create() : View
    {
        $specialties = Specialty::orderBy('name')->get();
        $branches = Branch::orderBy('name')->get();
        $roles = Role::where('guard_name', 'admin')->orderBy('name')->get();
        return view('admin.employees.create', compact('specialties', 'branches', 'roles'));
    }

    public function store(StoreRequest $request) : RedirectResponse
    {
        try {
            DB::beginTransaction();
            
            $validated = $request->validated();
            
            $tempPassword = Str::random(8);
            $validated['password'] = Hash::make($tempPassword);

            $employee = Employee::create($validated);
            
            if ($request->filled('specialties')) {
                $employee->specialties()->attach($request->input('specialties'));
            }
            if ($request->filled('branches')) {
                $employee->branches()->attach($request->input('branches'));
            }
            if ($request->filled('roles')) {
                $roleIds = $request->input('roles');
                $roleNames = Role::whereIn('id', $roleIds)->pluck('name')->toArray();
                $employee->syncRoles($roleNames);
            }
            
            DB::commit();
            
            Log::info('Сотрудник успешно создан', [
                'employee_id' => $employee->id,
                'employee_name' => $employee->name,
                'email' => $employee->email,
                'specialties_count' => $request->filled('specialties') ? count($request->input('specialties')) : 0,
                'branches_count' => $request->filled('branches') ? count($request->input('branches')) : 0
            ]);
            
            // Отправляем временный пароль на email сотрудника
            try {
                Mail::to($employee->email)->send(new EmployeePasswordReset(
                    $tempPassword,
                    $employee->name,
                    $employee->email
                ));
                
                Log::info('Письмо с временным паролем отправлено на email', [
                    'employee_id' => $employee->id,
                    'employee_email' => $employee->email
                ]);
                
                return redirect()->route('admin.employees.index')
                    ->with('success', 'Сотрудник успешно создан. Временный пароль отправлен на email');
                    
            } catch (\Exception $mailException) {
                Log::error('Ошибка при отправке письма с временным паролем', [
                    'employee_id' => $employee->id,
                    'employee_email' => $employee->email,
                    'error' => $mailException->getMessage()
                ]);
                
                // Сотрудник создан, но письмо не отправлено
                return redirect()->route('admin.employees.index')
                    ->with('warning', 'Сотрудник создан, но не удалось отправить письмо. Временный пароль: ' . $tempPassword);
            }
                
        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Ошибка при создании сотрудника', [
                'data' => $request->validated(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return back()
                ->withInput()
                ->withErrors(['error' => 'Ошибка при создании сотрудника: ' . $e->getMessage()]);
        }
    }

    public function edit($id) : View
    {
        $employee = Employee::with(['specialties', 'branches', 'roles'])->findOrFail($id);
        $specialties = Specialty::orderBy('name')->get();
        $branches = Branch::orderBy('name')->get();
        $roles = Role::where('guard_name', 'admin')->orderBy('name')->get();
        return view('admin.employees.edit', compact('employee', 'specialties', 'branches', 'roles'));
    }

    public function update(UpdateRequest $request, $id) : RedirectResponse
    {
        try {
            DB::beginTransaction();
            
            $employee = Employee::findOrFail($id);
            $validated = $request->validated();
            
            $oldName = $employee->name;
            $oldEmail = $employee->email;
            
            $employee->update($validated);
            $employee->specialties()->sync($request->input('specialties', []));
            $employee->branches()->sync($request->input('branches', []));
            
            // Синхронизируем роли - конвертируем ID в имена
            $roleIds = $request->input('roles', []);
            if (!empty($roleIds)) {
                $roleNames = Role::whereIn('id', $roleIds)->pluck('name')->toArray();
                $employee->syncRoles($roleNames);
            } else {
                $employee->syncRoles([]);
            }
            
            DB::commit();
            
            Log::info('Данные сотрудника успешно обновлены', [
                'employee_id' => $employee->id,
                'old_name' => $oldName,
                'new_name' => $employee->name,
                'old_email' => $oldEmail,
                'new_email' => $employee->email,
                'specialties_count' => count($request->input('specialties', [])),
                'branches_count' => count($request->input('branches', []))
            ]);
            
            return redirect()->route('admin.employees.index')
                ->with('success', 'Данные сотрудника успешно обновлены');
                
        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Ошибка при обновлении сотрудника', [
                'employee_id' => $id,
                'data' => $request->validated(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return back()
                ->withInput()
                ->withErrors(['error' => 'Ошибка при обновлении сотрудника: ' . $e->getMessage()]);
        }
    }

    public function destroy($id) : RedirectResponse
    {
        try {
            DB::beginTransaction();
            
            $employee = Employee::findOrFail($id);
            
            // Убираем проверку зависимостей - связи со специализациями удаляются каскадно
            $employeeName = $employee->name;
            $employeeEmail = $employee->email;
            
            // Удаляем сотрудника (связи удалятся каскадно)
            $employee->delete();
            
            DB::commit();
            
            Log::info('Сотрудник успешно удален', [
                'employee_id' => $id,
                'employee_name' => $employeeName,
                'employee_email' => $employeeEmail
            ]);
            
            return redirect()->route('admin.employees.index')
                ->with('success', 'Сотрудник успешно удалён');
                
        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Ошибка при удалении сотрудника', [
                'employee_id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return back()
                ->withErrors(['error' => 'Ошибка при удалении сотрудника: ' . $e->getMessage()]);
        }
    }

    public function resetPassword($id) : RedirectResponse
    {
        try {
            DB::beginTransaction();
            
            $employee = Employee::findOrFail($id);
            $tempPassword = Str::random(8);
            $employee->update(['password' => Hash::make($tempPassword)]);
            
            DB::commit();
            
            Log::info('Пароль сотрудника успешно сброшен', [
                'employee_id' => $id,
                'employee_name' => $employee->name,
                'employee_email' => $employee->email
            ]);
            
            // Отправляем новый пароль на email сотрудника
            try {
                Mail::to($employee->email)->send(new EmployeePasswordReset(
                    $tempPassword,
                    $employee->name,
                    $employee->email
                ));
                
                Log::info('Письмо с новым паролем отправлено на email', [
                    'employee_id' => $id,
                    'employee_email' => $employee->email
                ]);
                
                return redirect()->route('admin.employees.index')
                    ->with('success', 'Пароль успешно сброшен и отправлен на email сотрудника');
                    
            } catch (\Exception $mailException) {
                Log::error('Ошибка при отправке письма с новым паролем', [
                    'employee_id' => $id,
                    'employee_email' => $employee->email,
                    'error' => $mailException->getMessage()
                ]);
                
                // Пароль сброшен, но письмо не отправлено
                return redirect()->route('admin.employees.index')
                    ->with('warning', 'Пароль сброшен, но не удалось отправить письмо. Новый пароль: ' . $tempPassword);
            }
                
        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Ошибка при сбросе пароля сотрудника', [
                'employee_id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return back()
                ->withErrors(['error' => 'Ошибка при сбросе пароля: ' . $e->getMessage()]);
        }
    }

    public function show($id): View
    {
        $employee = Employee::with(['specialties', 'branches', 'roles'])->findOrFail($id);
        
        // Получаем общее количество записей
        $ordersTotal = Order::where('manager_id', $id)->count();
        $vaccinationsTotal = Vaccination::where('veterinarian_id', $id)->count();
        $labTestsTotal = LabTest::where('veterinarian_id', $id)->count();
        $schedulesTotal = Schedule::where('veterinarian_id', $id)->count();
        
        // Загружаем ограниченные данные для отображения
        $orders = Order::where('manager_id', $id)
            ->with(['client', 'pet', 'items.item'])
            ->latest()
            ->limit(10)
            ->get();
            
        $vaccinations = Vaccination::where('veterinarian_id', $id)
            ->with(['pet.client', 'vaccinationType.drugs'])
            ->latest()
            ->limit(10)
            ->get();
            
        $labTests = LabTest::where('veterinarian_id', $id)
            ->with(['pet.client', 'labTestType'])
            ->latest()
            ->limit(10)
            ->get();
            
        $schedules = Schedule::where('veterinarian_id', $id)
            ->with(['branch'])
            ->latest()
            ->limit(10)
            ->get();
        
        return view('admin.employees.show', compact('employee', 'orders', 'vaccinations', 'labTests', 'schedules', 'ordersTotal', 'vaccinationsTotal', 'labTestsTotal', 'schedulesTotal'));
    }

    public function specialtyOptions(Request $request)
    {
        return app(\App\Services\Options\SpecialtyOptionsService::class)->getOptions($request);
    }
}
