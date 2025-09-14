<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Service;
use App\Models\Branch;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ServiceController extends Controller
{
    /**
     * Каталог услуг
     */
    public function index(Request $request): View
    {
        $query = Service::with('branches');

        // Фильтрация по филиалу
        if ($request->filled('branch_id')) {
            $query->whereHas('branches', function($q) use ($request) {
                $q->where('branches.id', $request->branch_id);
            });
        }

        // Поиск по названию
        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        // Сортировка
        $sort = $request->get('sort', 'name');
        $direction = $request->get('direction', 'asc');
        
        if (in_array($sort, ['name', 'price', 'duration'])) {
            $query->orderBy($sort, $direction);
        }

        $services = $query->paginate(12);
        $branches = Branch::all();

        return view('client.services.index', compact('services', 'branches'));
    }

    /**
     * Детальная страница услуги
     */
    public function show(Service $service): View
    {
        $service->load('branches');
        
        // Получаем похожие услуги (из тех же филиалов)
        $relatedServices = Service::whereHas('branches', function($query) use ($service) {
            $query->whereIn('branches.id', $service->branches->pluck('id'));
        })
        ->where('id', '!=', $service->id)
        ->take(4)
        ->get();

        return view('client.services.show', compact('service', 'relatedServices'));
    }
}
