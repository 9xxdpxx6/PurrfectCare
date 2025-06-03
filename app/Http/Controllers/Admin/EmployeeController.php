<?php

namespace App\Http\Controllers\Admin;

use App\Models\Employee;
use App\Models\Specialty;
use App\Models\Branch;
use App\Http\Filters\EmployeeFilter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class EmployeeController extends AdminController
{
    public function __construct()
    {
        $this->model = Employee::class;
        $this->viewPath = 'employees';
        $this->routePrefix = 'employees';
        $this->validationRules = [
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:employees,email',
            'phone' => 'required|string|max:20',
            'specialization' => 'nullable|string|max:255',
            'hire_date' => 'required|date',
            'salary' => 'required|numeric|min:0',
            'is_active' => 'required|boolean'
        ];
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

    public function store(Request $request) : RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:employees,email',
            'phone' => 'required|string|max:20',
            'specialties' => 'nullable|array',
            'specialties.*' => 'exists:specialties,id',
            'branches' => 'nullable|array',
            'branches.*' => 'exists:branches,id',
        ]);
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

    public function update(Request $request, $id) : RedirectResponse
    {
        $employee = Employee::findOrFail($id);
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:employees,email,' . $employee->id,
            'phone' => 'required|string|max:20',
            'specialties' => 'nullable|array',
            'specialties.*' => 'exists:specialties,id',
            'branches' => 'nullable|array',
            'branches.*' => 'exists:branches,id',
        ]);
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
}
