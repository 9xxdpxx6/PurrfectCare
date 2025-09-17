<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Visit;
use App\Models\Status;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class VisitController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * История визитов
     */
    public function index(Request $request): View
    {
        $query = Visit::where('client_id', Auth::id())
            ->where('starts_at', '<', Carbon::now())
            ->with(['pet', 'schedule.veterinarian', 'schedule.branch', 'status']);

        // Фильтрация по статусу
        if ($request->filled('status')) {
            $query->whereHas('status', function($q) use ($request) {
                $q->where('name', $request->status);
            });
        }

        // Фильтрация по дате
        if ($request->filled('date_from')) {
            $query->whereDate('starts_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('starts_at', '<=', $request->date_to);
        }

        // Поиск по ветеринару или питомцу
        if ($request->filled('search')) {
            $query->where(function($q) use ($request) {
                // Поиск по ветеринару
                $q->whereHas('schedule.veterinarian', function($subQ) use ($request) {
                    $subQ->where('name', 'like', '%' . $request->search . '%');
                })
                // Поиск по питомцу (если есть)
                ->orWhereHas('pet', function($subQ) use ($request) {
                    $subQ->where('name', 'like', '%' . $request->search . '%');
                });
            });
        }

        $visits = $query->orderBy('starts_at', 'desc')->paginate(10);
        
        // Получаем все статусы для фильтра
        $statuses = Status::orderBy('name')->get();
        
        return view('client.profile.visits.index', compact('visits', 'statuses'));
    }

    /**
     * Детальная информация о визите
     */
    public function show(Visit $visit): View
    {
        // Проверяем, что визит принадлежит текущему пользователю
        if ($visit->client_id !== Auth::id()) {
            abort(403, 'Доступ запрещен');
        }

        $visit->load(['pet', 'schedule.veterinarian', 'schedule.branch', 'status']);
        
        return view('client.profile.visits.show', compact('visit'));
    }

    /**
     * Отмена визита
     */
    public function cancel(Visit $visit): RedirectResponse
    {
        // Проверяем, что визит принадлежит текущему пользователю
        if ($visit->client_id !== Auth::id()) {
            abort(403, 'Доступ запрещен');
        }

        // Проверяем, что визит можно отменить
        if ($visit->status->name !== 'Запланирован') {
            return back()->withErrors(['error' => 'Этот визит нельзя отменить.']);
        }

        // Проверяем время (можно отменить не менее чем за 2 часа)
        if ($visit->starts_at->diffInHours(now()) < 2) {
            return back()->withErrors(['error' => 'Отмена возможна не менее чем за 2 часа до визита.']);
        }

        // Находим статус "Отменен"
        $cancelledStatus = Status::where('name', 'Отменен')->first();
        
        if (!$cancelledStatus) {
            return back()->withErrors(['error' => 'Статус "Отменен" не найден.']);
        }

        $visit->update(['status_id' => $cancelledStatus->id]);

        return back()->with('success', 'Визит успешно отменен.');
    }

    /**
     * Повторная запись
     */
    public function reschedule(Visit $visit): RedirectResponse
    {
        // Проверяем, что визит принадлежит текущему пользователю
        if ($visit->client_id !== Auth::id()) {
            abort(403, 'Доступ запрещен');
        }

        // Перенаправляем на страницу записи с предзаполненными данными
        return redirect()->route('client.appointment.branches', [
            'veterinarian_id' => $visit->schedule->veterinarian_id,
            'branch_id' => $visit->schedule->branch_id
        ]);
    }
}
