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
