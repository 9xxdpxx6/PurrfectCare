<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Http\Requests\Admin\Branch\StoreRequest;
use App\Http\Requests\Admin\Branch\UpdateRequest;
use Illuminate\Http\Request;

class BranchController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Branch::query();

        // Поиск
        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('address', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Сортировка
        $sortField = $request->get('sort', 'name');
        $sortDirection = $request->get('direction', 'asc');
        $query->orderBy($sortField, $sortDirection);

        $branches = $query->paginate(15);

        return view('admin.branches.index', compact('branches'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.branches.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreRequest $request)
    {
        $branch = Branch::create($request->validated());

        return redirect()
            ->route('admin.branches.index')
            ->with('success', 'Филиал успешно создан');
    }

    /**
     * Display the specified resource.
     */
    public function show(Branch $branch)
    {
        return view('admin.branches.show', compact('branch'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Branch $branch)
    {
        return view('admin.branches.edit', compact('branch'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateRequest $request, Branch $branch)
    {
        $branch->update($request->validated());

        return redirect()
            ->route('admin.branches.index')
            ->with('success', 'Филиал успешно обновлен');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Branch $branch)
    {
        // Убираем проверку зависимостей - связи с услугами удаляются каскадно

        $branch->delete();

        return redirect()
            ->route('admin.branches.index')
            ->with('success', 'Филиал успешно удален');
    }
} 