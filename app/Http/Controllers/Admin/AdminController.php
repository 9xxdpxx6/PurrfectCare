<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

abstract class AdminController extends Controller
{
    protected $model;
    protected $viewPath;
    protected $routePrefix;
    protected $validationRules = [];
    protected $permissionPrefix;

    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            $this->checkPermissions($request);
            return $next($request);
        });
    }

    protected function checkPermissions(Request $request)
    {
        $user = auth()->guard('admin')->user();
        
        if (!$user) {
            abort(403, 'Unauthorized');
        }

        // Super admin имеет доступ ко всему
        if ($user->hasRole('super-admin')) {
            return;
        }

        // Определяем действие и проверяем разрешение
        $action = $this->getActionFromRequest($request);
        $permission = $this->getPermissionForAction($action);
        
        if ($permission && !$user->can($permission)) {
            abort(403, 'У вас нет прав для выполнения этого действия');
        }
    }

    protected function getActionFromRequest(Request $request): string
    {
        $routeName = $request->route()->getName();
        
        if (str_contains($routeName, '.create')) return 'create';
        if (str_contains($routeName, '.edit')) return 'update';
        if (str_contains($routeName, '.destroy')) return 'delete';
        if (str_contains($routeName, '.store')) return 'create';
        if (str_contains($routeName, '.update')) return 'update';
        
        return 'read';
    }

    protected function getPermissionForAction(string $action): ?string
    {
        if (!$this->permissionPrefix) {
            // Автоматически определяем префикс из routePrefix
            $this->permissionPrefix = $this->routePrefix;
        }
        
        return "{$this->permissionPrefix}.{$action}";
    }

    public function index(Request $request): View
    {
        $items = $this->model::paginate(100);
        return view("admin.{$this->viewPath}.index", compact('items'));
    }

    public function create(): View
    {
        return view("admin.{$this->viewPath}.create");
    }

    public function edit($id): View
    {
        $item = $this->model::findOrFail($id);
        return view("admin.{$this->viewPath}.edit", compact('item'));
    }

    public function destroy($id): RedirectResponse
    {
        try {
            DB::beginTransaction();
            
            $item = $this->model::findOrFail($id);
            
            // Проверяем наличие зависимых записей
            if ($errorMessage = $item->hasDependencies()) {
                throw new \Exception($errorMessage);
            }
            
            $itemName = $item->name ?? 'Запись';
            $item->delete();
            
            DB::commit();
            
            Log::info('Запись успешно удалена', [
                'model' => $this->model,
                'item_id' => $id,
                'item_name' => $itemName
            ]);

            return redirect()
                ->route("admin.{$this->routePrefix}.index")
                ->with('success', 'Запись успешно удалена');
                
        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Ошибка при удалении записи', [
                'model' => $this->model,
                'item_id' => $id,
                'error' => $e->getMessage()
            ]);
            
            return back()
                ->withErrors(['error' => 'Ошибка при удалении записи: ' . $e->getMessage()]);
        }
    }
}
