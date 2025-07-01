<?php

namespace App\Http\Controllers\Admin;

use App\Models\Employee;
use App\Models\Specialty;
use App\Models\Branch;
use App\Http\Requests\Admin\Employee\StoreRequest;
use App\Http\Requests\Admin\Employee\UpdateRequest;
use App\Http\Filters\EmployeeFilter;
use App\Http\Traits\HasSelectOptions;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class EmployeeController extends AdminController
{
    use HasSelectOptions;

    public function __construct()
    {
        $this->model = Employee::class;
        $this->viewPath = 'employees';
        $this->routePrefix = 'employees';
    }

        public function index(Request $request) : View
    {
        $filter = app(EmployeeFilter::class, ['queryParams' => array_filter($request->all())]);
        $employees = Employee::filter($filter)
            ->with(['specialties', 'branches'])
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
        return view('admin.employees.create', compact('specialties', 'branches'));
    }

    public function store(StoreRequest $request) : RedirectResponse
    {
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
        
        // TODO: отправить временный пароль на email
        return redirect()->route('admin.employees.index')
            ->with('success', 'Сотрудник успешно создан. Временный пароль: ' . $tempPassword);
    }

    public function edit($id) : View
    {
        $employee = Employee::with(['specialties', 'branches'])->findOrFail($id);
        $specialties = Specialty::orderBy('name')->get();
        $branches = Branch::orderBy('name')->get();
        return view('admin.employees.edit', compact('employee', 'specialties', 'branches'));
    }

    public function update(UpdateRequest $request, $id) : RedirectResponse
    {
        $employee = Employee::findOrFail($id);
        $validated = $request->validated();
        
        $employee->update($validated);
        $employee->specialties()->sync($request->input('specialties', []));
        $employee->branches()->sync($request->input('branches', []));
        
        return redirect()->route('admin.employees.index')
            ->with('success', 'Данные сотрудника успешно обновлены');
    }

    public function destroy($id) : RedirectResponse
    {
        $employee = Employee::findOrFail($id);
        $employee->specialties()->detach();
        $employee->branches()->detach();
        $employee->delete();
        return redirect()->route('admin.employees.index')
            ->with('success', 'Сотрудник успешно удалён');
    }

    public function resetPassword($id) : RedirectResponse
    {
        $employee = Employee::findOrFail($id);
        $tempPassword = Str::random(8);
        $employee->update(['password' => Hash::make($tempPassword)]);
        // TODO: отправить новый временный пароль на email
        return redirect()->route('admin.employees.index')
            ->with('success', 'Пароль успешно сброшен. Новый временный пароль: ' . $tempPassword);
    }

    public function show($id): View
    {
        $employee = Employee::with(['specialties', 'branches'])->findOrFail($id);
        return view('admin.employees.show', compact('employee'));
    }
}
