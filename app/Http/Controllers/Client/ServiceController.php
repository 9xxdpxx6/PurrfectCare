<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Service;
use App\Models\VaccinationType;
use App\Models\LabTestType;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ServiceController extends Controller
{
    /**
     * Каталог услуг
     */
    public function index(Request $request): View
    {
        $type = $request->get('type', 'services');
        $items = collect();
        
        // Получаем данные в зависимости от типа
        if ($type === 'vaccinations') {
            $query = VaccinationType::query();
        } elseif ($type === 'analyses') {
            $query = LabTestType::query();
        } else {
            $query = Service::with('branches');
        }


        // Поиск по названию
        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        // Сортировка
        $sort = $request->get('sort', 'name_asc');
        $direction = 'asc';
        
        if (str_ends_with($sort, '_asc')) {
            $sort = str_replace('_asc', '', $sort);
            $direction = 'asc';
        } elseif (str_ends_with($sort, '_desc')) {
            $sort = str_replace('_desc', '', $sort);
            $direction = 'desc';
        }
        
        if (in_array($sort, ['name', 'price'])) {
            $query->orderBy($sort, $direction);
        } elseif ($sort === 'duration' && $type === 'services') {
            $query->orderBy('duration', $direction);
        }

        $items = $query->paginate(12);

        return view('client.services.index', compact('items', 'type'));
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
