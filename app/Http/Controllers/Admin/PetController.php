<?php

namespace App\Http\Controllers\Admin;

use App\Models\Pet;
use App\Models\User;
use App\Models\Breed;
use App\Http\Requests\Admin\Pet\StoreRequest;
use App\Http\Requests\Admin\Pet\UpdateRequest;
use App\Http\Traits\HasOptionsMethods;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use App\Http\Filters\PetFilter;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Services\Export\ExportService;

class PetController extends AdminController
{
    use HasOptionsMethods;

    public function __construct()
    {
        parent::__construct();
        $this->model = Pet::class;
        $this->viewPath = 'pets';
        $this->routePrefix = 'pets';
        $this->permissionPrefix = 'pets';
    }

    public function create() : View
    {
        // Получаем ID клиента из параметра запроса
        $selectedClientId = request('owner');
        
        // Загружаем список клиентов для отображения информации о выбранном клиенте
        $clients = User::select(['id', 'name', 'email'])->orderBy('name')->get();
        
        return view("admin.{$this->viewPath}.create", compact('selectedClientId', 'clients'));
    }

    public function edit($id) : View
    {
        // Оптимизация: используем индексы на внешние ключи и select для выбора нужных полей
        $item = $this->model::select([
                'id', 'name', 'client_id', 'breed_id', 'gender', 'birthdate',
                'temperature', 'weight', 'created_at', 'updated_at'
            ])
            ->findOrFail($id);
            
        return view("admin.{$this->viewPath}.edit", compact('item'));
    }

    public function store(StoreRequest $request) : RedirectResponse
    {
        try {
            DB::beginTransaction();
            
            $validated = $request->validated();
            
            $pet = $this->model::create($validated);
            
            DB::commit();
            
            Log::info('Питомец успешно создан', [
                'pet_id' => $pet->id,
                'pet_name' => $pet->name,
                'client_id' => $pet->client_id,
                'breed_id' => $pet->breed_id
            ]);
            
            return redirect()
                ->route("admin.{$this->routePrefix}.index")
                ->with('success', 'Питомец успешно создан');
                
        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Ошибка при создании питомца', [
                'data' => $request->validated(),
                'error' => $e->getMessage()
            ]);
            
            return back()
                ->withInput()
                ->withErrors(['error' => 'Ошибка при создании питомца: ' . $e->getMessage()]);
        }
    }

    public function update(UpdateRequest $request, $id) : RedirectResponse
    {
        try {
            DB::beginTransaction();
            
            $validated = $request->validated();
            
            $item = $this->model::findOrFail($id);
            $oldName = $item->name;
            $oldClientId = $item->client_id;
            $oldBreedId = $item->breed_id;
            
            $item->update($validated);
            
            DB::commit();
            
            Log::info('Данные питомца успешно обновлены', [
                'pet_id' => $item->id,
                'old_name' => $oldName,
                'new_name' => $item->name,
                'old_client_id' => $oldClientId,
                'new_client_id' => $item->client_id,
                'old_breed_id' => $oldBreedId,
                'new_breed_id' => $item->breed_id
            ]);
            
            return redirect()
                ->route("admin.{$this->routePrefix}.index")
                ->with('success', 'Данные питомца успешно обновлены');
                
        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Ошибка при обновлении питомца', [
                'pet_id' => $id,
                'data' => $request->validated(),
                'error' => $e->getMessage()
            ]);
            
            return back()
                ->withInput()
                ->withErrors(['error' => 'Ошибка при обновлении питомца: ' . $e->getMessage()]);
        }
    }

    public function index(Request $request) : View
    {
        $filter = app()->make(PetFilter::class, ['queryParams' => array_filter($request->all())]);
        
        // Оптимизация: используем индексы на внешние ключи и select для выбора нужных полей
        $query = $this->model::select([
                'id', 'name', 'client_id', 'breed_id', 'gender', 'birthdate',
                'temperature', 'weight', 'created_at'
            ])
            ->with([
                'client:id,name,email,phone',
                'breed:id,name',
                'visits:id,pet_id,starts_at,status_id',
                'orders:id,pet_id,total,is_paid,closed_at',
                'vaccinations:id,pet_id,administered_at,next_due',
                'labTests:id,pet_id,received_at,completed_at'
            ]);
            
        $filter->apply($query);
        
        $items = $query->paginate(25)->appends($request->query());
        
        // Подсчитаем статистику для каждого питомца
        foreach ($items as $pet) {
            $pet->visits_count = $pet->visits->count();
            $pet->orders_count = $pet->orders->count();
            $pet->vaccinations_count = $pet->vaccinations->count();
            $pet->lab_tests_count = $pet->labTests->count();
        }
        
        // Оптимизация: используем select для выбора только нужных полей
        $clients = User::select(['id', 'name', 'email'])->orderBy('name')->get();
        $breeds = Breed::select(['id', 'name'])
            ->orderBy('name')
            ->get();
        
        return view("admin.{$this->viewPath}.index", compact('items', 'clients', 'breeds'));
    }

    public function show($id) : View
    {
        // Оптимизация: используем индексы на внешние ключи и select для выбора нужных полей
        $pet = $this->model::select([
                'id', 'name', 'client_id', 'breed_id', 'gender', 'birthdate',
                'temperature', 'weight', 'created_at', 'updated_at'
            ])
            ->with([
                'client:id,name,email,phone,address',
                'breed:id,name,species_id',
                'breed.species:id,name'
            ])
            ->findOrFail($id);
        
        // Загружаем связанные данные с оптимизацией
        $visits = $pet->visits()
            ->select(['id', 'pet_id', 'schedule_id', 'starts_at', 'status_id', 'complaints'])
            ->with([
                'schedule:id,veterinarian_id,shift_starts_at',
                'schedule.veterinarian:id,name,email',
                'schedule.veterinarian.specialties:id,name',
                'status:id,name,color'
            ])
            ->latest()
            ->limit(10)
            ->get();
            
        $vaccinations = $pet->vaccinations()
            ->select(['id', 'pet_id', 'veterinarian_id', 'vaccination_type_id', 'administered_at', 'next_due'])
            ->with([
                'veterinarian:id,name,email',
                'veterinarian.specialties:id,name',
                'vaccinationType:id,name'
            ])
            ->latest()
            ->limit(10)
            ->get();
            
        $labTests = $pet->labTests()
            ->select(['id', 'pet_id', 'veterinarian_id', 'lab_test_type_id', 'received_at', 'completed_at'])
            ->with([
                'veterinarian:id,name,email',
                'veterinarian.specialties:id,name',
                'labTestType:id,name']
            )
            ->latest()
            ->limit(10)
            ->get();
            
        $orders = $pet->orders()
            ->select(['id', 'pet_id', 'branch_id', 'status_id', 'total', 'is_paid', 'closed_at', 'created_at'])
            ->with([
                'branch:id,name,address',
                'status:id,name,color'
            ])
            ->latest()
            ->limit(10)
            ->get();
        
        // Подсчитываем общие количества с оптимизацией
        $visitsTotal = $pet->visits()->count();
        $vaccinationsTotal = $pet->vaccinations()->count();
        $labTestsTotal = $pet->labTests()->count();
        $ordersTotal = $pet->orders()->count();
        
        return view("admin.{$this->viewPath}.show", compact(
            'pet', 'visits', 'vaccinations', 'labTests', 'orders',
            'visitsTotal', 'vaccinationsTotal', 'labTestsTotal', 'ordersTotal'
        ));
    }

    public function destroy($id) : RedirectResponse
    {
        try {
            DB::beginTransaction();
            
            // Оптимизация: используем select для выбора только нужных полей
            $item = $this->model::select(['id', 'name'])->findOrFail($id);
            
            // Проверяем наличие зависимых записей
            if ($errorMessage = $item->hasDependencies()) {
                throw new \Exception($errorMessage);
            }
            
            $petName = $item->name;
            $item->delete();
            
            DB::commit();
            
            Log::info('Питомец успешно удален', [
                'pet_id' => $id,
                'pet_name' => $petName
            ]);

            return redirect()
                ->route("admin.{$this->routePrefix}.index")
                ->with('success', 'Питомец успешно удален');
                
        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Ошибка при удалении питомца', [
                'pet_id' => $id,
                'error' => $e->getMessage()
            ]);
            
            return back()
                ->withErrors(['error' => 'Ошибка при удалении питомца: ' . $e->getMessage()]);
        }
    }

    public function export(Request $request)
    {
        try {
            $filter = app()->make(PetFilter::class, ['queryParams' => array_filter($request->all())]);
            
            // Оптимизация: используем индексы на внешние ключи и select для выбора нужных полей
            $query = $this->model::select([
                    'id', 'name', 'client_id', 'breed_id', 'gender', 'birthdate',
                    'temperature', 'weight', 'created_at'
                ])
                ->with([
                    'client:id,name,email,phone',
                    'breed:id,name',
                    'visits:id,pet_id,starts_at,status_id',
                    'orders:id,pet_id,total,is_paid,closed_at',
                    'vaccinations:id,pet_id,administered_at,next_due',
                    'labTests:id,pet_id,received_at,completed_at'
                ]);
                
            $filter->apply($query);
            
            // Ограничиваем количество записей для экспорта (максимум 2000)
            $data = $query->limit(2000)->get();
            
            // Подсчитаем статистику для каждого питомца
            foreach ($data as $pet) {
                $pet->visits_count = $pet->visits->count();
                $pet->orders_count = $pet->orders->count();
                $pet->vaccinations_count = $pet->vaccinations->count();
                $pet->lab_tests_count = $pet->labTests->count();
            }
            
            // Форматируем данные для экспорта
            $formattedData = $data->map(function ($pet) {
                return [
                    'ID' => $pet->id,
                    'Имя' => $pet->name,
                    'Владелец' => $pet->client ? $pet->client->name : 'Не указан',
                    'Email владельца' => $pet->client ? $pet->client->email : '',
                    'Телефон владельца' => $pet->client ? $pet->client->phone : '',
                    'Порода' => $pet->breed ? $pet->breed->name : 'Не указана',
                    'Пол' => $pet->gender === 'male' ? 'Самец' : ($pet->gender === 'female' ? 'Самка' : 'Неизвестно'),
                    'Дата рождения' => $pet->birthdate ? $pet->birthdate->format('d.m.Y') : '',
                    'Возраст (лет)' => $pet->birthdate ? $pet->birthdate->age : '',
                    'Температура' => $pet->temperature ? $pet->temperature . '°C' : '',
                    'Вес' => $pet->weight ? $pet->weight . ' кг' : '',
                    'Количество приемов' => $pet->visits_count,
                    'Количество заказов' => $pet->orders_count,
                    'Количество вакцинаций' => $pet->vaccinations_count,
                    'Количество анализов' => $pet->lab_tests_count,
                    'Дата регистрации' => $pet->created_at ? $pet->created_at->format('d.m.Y H:i') : '',
                ];
            });
            
            $filename = app(ExportService::class)->generateFilename('pets', 'xlsx');
            
            return app(ExportService::class)->toExcel($formattedData, $filename);
            
        } catch (\Exception $e) {
            Log::error('Ошибка при экспорте питомцев', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return back()->withErrors(['error' => 'Ошибка при экспорте: ' . $e->getMessage()]);
        }
    }

    /**
     * Экспорт медицинской истории питомца
     */
    public function exportMedicalHistory(Request $request, $petId, $format = 'pdf')
    {
        try {
            $pet = Pet::with([
                'client:id,name,email,phone,address',
                'breed:id,name,species_id',
                'breed.species:id,name',
                'visits' => function($query) {
                    $query->select(['id', 'pet_id', 'schedule_id', 'starts_at', 'status_id', 'complaints', 'notes', 'is_completed', 'created_at'])
                        ->with([
                            'schedule:id,veterinarian_id,branch_id,shift_starts_at',
                            'schedule.veterinarian:id,name,email',
                            'schedule.branch:id,name,address',
                            'status:id,name,color',
                            'symptoms:id,visit_id,dictionary_symptom_id,custom_symptom,notes',
                            'symptoms.dictionarySymptom:id,name',
                            'diagnoses:id,visit_id,dictionary_diagnosis_id,custom_diagnosis,treatment_plan',
                            'diagnoses.dictionaryDiagnosis:id,name',
                            'orders:id,client_id,pet_id,total,is_paid,closed_at'
                        ])
                        ->orderBy('starts_at', 'desc');
                },
                'vaccinations' => function($query) {
                    $query->select(['id', 'pet_id', 'veterinarian_id', 'vaccination_type_id', 'administered_at', 'next_due', 'created_at'])
                        ->with([
                            'veterinarian:id,name,email',
                            'vaccinationType:id,name'
                        ])
                        ->orderBy('administered_at', 'desc');
                },
                'labTests' => function($query) {
                    $query->select(['id', 'pet_id', 'veterinarian_id', 'lab_test_type_id', 'received_at', 'completed_at', 'created_at'])
                        ->with([
                            'veterinarian:id,name,email',
                            'labTestType:id,name'
                        ])
                        ->orderBy('received_at', 'desc');
                },
                'orders' => function($query) {
                    $query->select(['id', 'pet_id', 'branch_id', 'status_id', 'total', 'is_paid', 'closed_at', 'created_at'])
                        ->with([
                            'branch:id,name,address',
                            'status:id,name,color',
                            'items:id,order_id,item_type,item_id,quantity,unit_price'
                        ])
                        ->orderBy('created_at', 'desc');
                }
            ])->findOrFail($petId);


            // Форматируем данные для экспорта
            $formattedData = [
                'pet_info' => [
                    'ID' => $pet->id,
                    'Имя' => $pet->name,
                    'Владелец' => $pet->client ? $pet->client->name : 'Не указан',
                    'Email владельца' => $pet->client ? $pet->client->email : '',
                    'Телефон владельца' => $pet->client ? $pet->client->phone : '',
                    'Адрес владельца' => $pet->client ? $pet->client->address : '',
                    'Порода' => $pet->breed ? $pet->breed->name : 'Не указана',
                    'Вид' => $pet->breed && $pet->breed->species ? $pet->breed->species->name : 'Не указан',
                    'Пол' => $pet->gender === 'male' ? 'Самец' : ($pet->gender === 'female' ? 'Самка' : 'Неизвестно'),
                    'Дата рождения' => $pet->birthdate ? $pet->birthdate->format('d.m.Y') : '',
                    'Возраст' => $pet->birthdate ? $pet->birthdate->age . ' лет' : '',
                    'Температура' => $pet->temperature ? $pet->temperature . '°C' : 'Не измерена',
                    'Вес' => $pet->weight ? $pet->weight . ' кг' : 'Не измерен',
                    'Дата регистрации' => $pet->created_at ? $pet->created_at->format('d.m.Y H:i') : ''
                ],
                'visits' => $pet->visits->map(function($visit) {
                    return [
                        'ID' => $visit->id,
                        'Дата и время' => $visit->starts_at ? \Carbon\Carbon::parse($visit->starts_at)->format('d.m.Y H:i') : '',
                        'Ветеринар' => $visit->schedule && $visit->schedule->veterinarian ? $visit->schedule->veterinarian->name : 'Не указан',
                        'Email ветеринара' => $visit->schedule && $visit->schedule->veterinarian ? $visit->schedule->veterinarian->email : '',
                        'Филиал' => $visit->schedule && $visit->schedule->branch ? $visit->schedule->branch->name : 'Не указан',
                        'Адрес филиала' => $visit->schedule && $visit->schedule->branch ? $visit->schedule->branch->address : '',
                        'Статус' => $visit->status ? $visit->status->name : 'Не указан',
                        'Жалобы' => $visit->complaints ?: 'Не указаны',
                        'Заметки' => $visit->notes ?: 'Нет',
                        'Завершен' => $visit->is_completed ? 'Да' : 'Нет',
                        'Симптомы' => $visit->symptoms ? $visit->symptoms->map(function($symptom) {
                            return $symptom->dictionarySymptom ? $symptom->dictionarySymptom->name : $symptom->custom_symptom;
                        })->implode(', ') : 'Не указаны',
                        'Диагнозы' => $visit->diagnoses ? $visit->diagnoses->map(function($diagnosis) {
                            return $diagnosis->dictionaryDiagnosis ? $diagnosis->dictionaryDiagnosis->name : $diagnosis->custom_diagnosis;
                        })->implode(', ') : 'Не указаны',
                        'Планы лечения' => $visit->diagnoses ? $visit->diagnoses->map(function($diagnosis) {
                            return $diagnosis->treatment_plan ?: 'Не указан';
                        })->implode(', ') : 'Не указаны',
                        'Количество заказов' => $visit->orders ? $visit->orders->count() : 0,
                        'Общая сумма заказов' => $visit->orders ? number_format($visit->orders->sum('total'), 2, ',', ' ') . ' руб.' : '0,00 руб.'
                    ];
                }),
                'vaccinations' => $pet->vaccinations->map(function($vaccination) {
                    return [
                        'ID' => $vaccination->id,
                        'Тип вакцины' => $vaccination->vaccinationType ? $vaccination->vaccinationType->name : 'Не указан',
                        'Ветеринар' => $vaccination->veterinarian ? $vaccination->veterinarian->name : 'Не указан',
                        'Email ветеринара' => $vaccination->veterinarian ? $vaccination->veterinarian->email : '',
                        'Дата вакцинации' => $vaccination->administered_at ? \Carbon\Carbon::parse($vaccination->administered_at)->format('d.m.Y') : '',
                        'Следующая вакцинация' => $vaccination->next_due ? \Carbon\Carbon::parse($vaccination->next_due)->format('d.m.Y') : 'Не указана',
                        'Дата создания' => $vaccination->created_at ? $vaccination->created_at->format('d.m.Y H:i') : ''
                    ];
                }),
                'lab_tests' => $pet->labTests->map(function($labTest) {
                    return [
                        'ID' => $labTest->id,
                        'Тип анализа' => $labTest->labTestType ? $labTest->labTestType->name : 'Не указан',
                        'Ветеринар' => $labTest->veterinarian ? $labTest->veterinarian->name : 'Не указан',
                        'Email ветеринара' => $labTest->veterinarian ? $labTest->veterinarian->email : '',
                        'Дата получения' => $labTest->received_at ? \Carbon\Carbon::parse($labTest->received_at)->format('d.m.Y') : '',
                        'Дата завершения' => $labTest->completed_at ? \Carbon\Carbon::parse($labTest->completed_at)->format('d.m.Y') : 'Не завершен',
                        'Дата создания' => $labTest->created_at ? $labTest->created_at->format('d.m.Y H:i') : ''
                    ];
                }),
                'orders' => $pet->orders->map(function($order) {
                    return [
                        'ID' => $order->id,
                        'Филиал' => $order->branch ? $order->branch->name : 'Не указан',
                        'Адрес филиала' => $order->branch ? $order->branch->address : '',
                        'Статус' => $order->status ? $order->status->name : 'Не указан',
                        'Общая сумма' => number_format($order->total, 2, ',', ' ') . ' руб.',
                        'Оплачен' => $order->is_paid ? 'Да' : 'Нет',
                        'Дата закрытия' => $order->closed_at ? \Carbon\Carbon::parse($order->closed_at)->format('d.m.Y H:i') : 'Не закрыт',
                        'Дата создания' => $order->created_at ? $order->created_at->format('d.m.Y H:i') : '',
                        'Количество позиций' => $order->items ? $order->items->count() : 0
                    ];
                }),
                'summary' => [
                    'Общее количество приемов' => $pet->visits->count(),
                    'Общее количество вакцинаций' => $pet->vaccinations->count(),
                    'Общее количество анализов' => $pet->labTests->count(),
                    'Общее количество заказов' => $pet->orders->count(),
                    'Общая сумма заказов' => number_format($pet->orders->sum('total'), 2, ',', ' ') . ' руб.',
                    'Последний прием' => $pet->visits->first() ? \Carbon\Carbon::parse($pet->visits->first()->starts_at)->format('d.m.Y H:i') : 'Нет приемов',
                    'Последняя вакцинация' => $pet->vaccinations->first() ? \Carbon\Carbon::parse($pet->vaccinations->first()->administered_at)->format('d.m.Y') : 'Нет вакцинаций',
                    'Последний анализ' => $pet->labTests->first() ? \Carbon\Carbon::parse($pet->labTests->first()->received_at)->format('d.m.Y') : 'Нет анализов'
                ]
            ];

            $filename = app(ExportService::class)->generateFilename('medical_history_' . $pet->name, $format);
            
            if ($format === 'pdf') {
                return $this->exportMedicalHistoryPdf($formattedData, $filename);
            } else {
                return app(ExportService::class)->toExcel($formattedData, $filename);
            }
            
        } catch (\Exception $e) {
            Log::error('Ошибка при экспорте медицинской истории питомца', [
                'pet_id' => $petId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return back()->withErrors(['error' => 'Ошибка при экспорте: ' . $e->getMessage()]);
        }
    }

    /**
     * Экспорт медкарты в PDF
     */
    private function exportMedicalHistoryPdf($data, $filename)
    {
        // Извлекаем отдельные переменные из массива данных
        $petInfo = $data['pet_info'];
        $visits = $data['visits'];
        $vaccinations = $data['vaccinations'];
        $labTests = $data['lab_tests'];
        $orders = $data['orders'];
        $summary = $data['summary'];
        
        $html = view('admin.exports.medical-history-pdf', compact(
            'petInfo', 'visits', 'vaccinations', 'labTests', 'orders', 'summary'
        ))->render();
        
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadHTML($html);
        $pdf->setPaper('A4', 'portrait');
        $pdf->setOptions([
            'defaultFont' => 'DejaVu Sans',
            'isRemoteEnabled' => false,
            'isHtml5ParserEnabled' => true,
            'isPhpEnabled' => true,
            'defaultMediaType' => 'print',
            'isFontSubsettingEnabled' => true,
        ]);
        
        return $pdf->download($filename);
    }

} 