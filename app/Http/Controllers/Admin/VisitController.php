<?php

namespace App\Http\Controllers\Admin;

use App\Http\Traits\HasOptionsMethods;
use App\Models\Visit;
use App\Models\User;
use App\Models\Pet;
use App\Models\Schedule;
use App\Models\Status;
use App\Models\Service;
use App\Models\Symptom;
use App\Models\Diagnosis;
use App\Models\DictionarySymptom;
use App\Models\DictionaryDiagnosis;
use App\Http\Filters\VisitFilter;
use App\Services\Visit\VisitManagementService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use App\Http\Requests\Admin\Visit\StoreRequest;
use App\Http\Requests\Admin\Visit\UpdateRequest;
use Illuminate\Support\Facades\DB;
use App\Services\Export\ExportService;
use Illuminate\Support\Facades\Log;

class VisitController extends AdminController
{
    use HasOptionsMethods;
    
    protected $visitService;
    
    public function __construct(VisitManagementService $visitService)
    {
        parent::__construct();
        $this->visitService = $visitService;
        $this->model = Visit::class;
        $this->viewPath = 'visits';
        $this->routePrefix = 'visits';
        $this->permissionPrefix = 'visits';
    }

    public function create() : View
    {
        // Получаем ID клиента, питомца и расписания из параметров запроса
        $selectedClientId = request('client');
        $selectedPetId = request('pet');
        $selectedScheduleId = request('schedule_id');
        
        // Если передан pet_id, но не передан client_id, получаем владельца питомца
        if ($selectedPetId && !$selectedClientId) {
            $pet = Pet::select(['id', 'client_id'])
                ->with(['client:id,name,email'])
                ->find($selectedPetId);
            if ($pet && $pet->client) {
                $selectedClientId = $pet->client->id;
            }
        }
        
        // Получаем только статус по умолчанию
        $default_status = Status::select(['id', 'name', 'color'])->where('name', 'Новый')->first();
        $default_status_id = $default_status ? $default_status->id : null;
        
        return view("admin.{$this->viewPath}.create", compact(
            'default_status_id', 'selectedClientId', 'selectedPetId', 'selectedScheduleId'
        ));
    }

    public function edit($id) : View
    {
        // Загружаем только данные визита с нужными связями
        $item = $this->model::select([
                'id', 'client_id', 'pet_id', 'schedule_id', 'starts_at', 'status_id',
                'complaints', 'notes', 'is_completed', 'created_at', 'updated_at'
            ])
            ->with([
                'diagnoses:id,visit_id,dictionary_diagnosis_id,custom_diagnosis,treatment_plan',
                'diagnoses.dictionaryDiagnosis:id,name',
                'symptoms:id,visit_id,dictionary_symptom_id,custom_symptom,notes',
                'symptoms.dictionarySymptom:id,name'
            ])
            ->findOrFail($id);
        
        // Подготавливаем выбранные симптомы
        $selectedSymptoms = $item->symptoms->map(function($symptom) {
            if ($symptom->dictionary_symptom_id) {
                return [
                    'id' => $symptom->dictionary_symptom_id,
                    'name' => $symptom->dictionarySymptom->name
                ];
            } else {
                return [
                    'id' => $symptom->custom_symptom,
                    'name' => $symptom->custom_symptom
                ];
            }
        });
        
        // Подготавливаем выбранные диагнозы
        $selectedDiagnoses = $item->diagnoses->map(function($diagnosis) {
            if ($diagnosis->dictionary_diagnosis_id) {
                return [
                    'id' => $diagnosis->dictionary_diagnosis_id,
                    'name' => $diagnosis->dictionaryDiagnosis->name,
                    'treatment_plan' => $diagnosis->treatment_plan,
                    'pivot_id' => $diagnosis->id
                ];
            } else {
                return [
                    'id' => $diagnosis->custom_diagnosis,
                    'name' => $diagnosis->custom_diagnosis,
                    'treatment_plan' => $diagnosis->treatment_plan,
                    'pivot_id' => $diagnosis->id
                ];
            }
        });
        
        return view("admin.{$this->viewPath}.edit", compact(
            'item', 'selectedSymptoms', 'selectedDiagnoses'
        ));
    }

    public function index(Request $request) : View
    {
        // Преобразуем даты из формата d.m.Y в Y-m-d для фильтров
        $queryParams = $request->query();
        if (isset($queryParams['date_from']) && $queryParams['date_from']) {
            try {
                $queryParams['date_from'] = \Carbon\Carbon::createFromFormat('d.m.Y', $queryParams['date_from'])->format('Y-m-d');
            } catch (\Exception $e) {
                // Если не удается преобразовать, оставляем как есть
            }
        }
        if (isset($queryParams['date_to']) && $queryParams['date_to']) {
            try {
                $queryParams['date_to'] = \Carbon\Carbon::createFromFormat('d.m.Y', $queryParams['date_to'])->format('Y-m-d');
            } catch (\Exception $e) {
                // Если не удается преобразовать, оставляем как есть
            }
        }
        
        $filter = app(VisitFilter::class, ['queryParams' => $queryParams]);
        
        // Оптимизация: используем индексы на внешние ключи и select для выбора нужных полей
        $query = $this->model::select([
                'id', 'client_id', 'pet_id', 'schedule_id', 'starts_at', 'status_id',
                'complaints', 'notes', 'is_completed', 'created_at'
            ])
            ->with([
                'client:id,name,email',
                'pet:id,name,breed_id',
                'schedule:id,veterinarian_id,branch_id,shift_starts_at',
                'status:id,name,color',
                'symptoms:id,visit_id,dictionary_symptom_id,custom_symptom',
                'symptoms.dictionarySymptom:id,name',
                'diagnoses:id,visit_id,dictionary_diagnosis_id,custom_diagnosis',
                'diagnoses.dictionaryDiagnosis:id,name',
                'orders:id,client_id,pet_id,total,is_paid'
            ])
            ->filter($filter);
            
        $items = $query->paginate(25)->withQueryString();
        
        // Подготавливаем данные для каждого приёма
        foreach ($items as $visit) {
            $formattedData = $this->visitService->getFormattedDisplayData($visit);
            $visit->symptoms_display = $formattedData['symptoms']['display'];
            $visit->diagnoses_display = $formattedData['diagnoses']['display'];
        }
        
        // Оптимизация: используем select для выбора только нужных полей
        $statuses = Status::select(['id', 'name', 'color'])->orderBy('name')->get();
        
        return view("admin.{$this->viewPath}.index", compact('items', 'statuses'));
    }

    public function show($id) : View
    {
        // Оптимизация: используем индексы на внешние ключи и select для выбора нужных полей
        $item = $this->model::select([
                'id', 'client_id', 'pet_id', 'schedule_id', 'starts_at', 'status_id',
                'complaints', 'notes', 'is_completed', 'created_at', 'updated_at'
            ])
            ->with([
                'client:id,name,email,phone,address',
                'pet:id,name,breed_id,client_id,birthdate,gender',
                'schedule:id,veterinarian_id,branch_id,shift_starts_at,shift_ends_at',
                'schedule.veterinarian:id,name,email',
                'status:id,name,color',
                'symptoms:id,visit_id,dictionary_symptom_id,custom_symptom,notes',
                'symptoms.dictionarySymptom:id,name',
                'diagnoses:id,visit_id,dictionary_diagnosis_id,custom_diagnosis,treatment_plan',
                'diagnoses.dictionaryDiagnosis:id,name',
                'orders:id,client_id,pet_id,status_id,total,is_paid,closed_at'
            ])
            ->findOrFail($id);
        return view("admin.{$this->viewPath}.show", compact('item'));
    }

    public function store(StoreRequest $request) : RedirectResponse
    {
        $validated = $request->validated();
        $this->visitService->createVisit($validated, $request);
        
        return redirect()
            ->route("admin.{$this->routePrefix}.index")
            ->with('success', 'Запись на прием успешно создана');
    }

    public function update(UpdateRequest $request, $id) : RedirectResponse
    {
        $validated = $request->validated();
        $this->visitService->updateVisit($id, $validated, $request);
        
        return redirect()
            ->route("admin.{$this->routePrefix}.index")
            ->with('success', 'Запись на прием успешно обновлена');
    }

    public function destroy($id) : RedirectResponse
    {
        $this->visitService->deleteVisit($id);
        return redirect()->route("admin.{$this->routePrefix}.index")
            ->with('success', 'Запись на приём успешно удалена');
    }

    /**
     * Получить доступное время для выбранного расписания
     */
    public function getAvailableTime(Request $request)
    {
        $scheduleId = $request->input('schedule_id');
        
        if (!$scheduleId) {
            return response()->json(['error' => 'Не выбрано расписание']);
        }
        
        try {
            $availableTime = $this->visitService->getAvailableTime($scheduleId);
            return response()->json($availableTime);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()]);
        }
    }

    // TomSelect опции для основных полей
    public function clientOptions(Request $request)
    {
        return app(\App\Services\Options\ClientOptionsService::class)->getOptions($request);
    }

    public function petOptions(Request $request)
    {
        return app(\App\Services\Options\PetOptionsService::class)->getOptions($request);
    }

    public function scheduleOptions(Request $request)
    {
        return app(\App\Services\Options\ScheduleOptionsService::class)->getOptions($request);
    }

    public function statusOptions(Request $request)
    {
        return app(\App\Services\Options\StatusOptionsService::class)->getOptions($request);
    }

    public function symptomOptions(Request $request)
    {
        return app(\App\Services\Options\SymptomOptionsService::class)->getOptions($request);
    }

    public function diagnosisOptions(Request $request)
    {
        return app(\App\Services\Options\DiagnosisOptionsService::class)->getOptions($request);
    }

    /**
     * Экспорт визитов
     */
    public function export(Request $request)
    {
        try {
            // Преобразуем даты из формата d.m.Y в Y-m-d для фильтров
            $queryParams = $request->query();
            if (isset($queryParams['date_from']) && $queryParams['date_from']) {
                try {
                    $queryParams['date_from'] = \Carbon\Carbon::createFromFormat('d.m.Y', $queryParams['date_from'])->format('Y-m-d');
                } catch (\Exception $e) {
                    // Если не удается преобразовать, оставляем как есть
                }
            }
            if (isset($queryParams['date_to']) && $queryParams['date_to']) {
                try {
                    $queryParams['date_to'] = \Carbon\Carbon::createFromFormat('d.m.Y', $queryParams['date_to'])->format('Y-m-d');
                } catch (\Exception $e) {
                    // Если не удается преобразовать, оставляем как есть
                }
            }
            
            $filter = app(VisitFilter::class, ['queryParams' => $queryParams]);
            
            // Оптимизация: используем индексы на внешние ключи и select для выбора нужных полей
            $query = $this->model::select([
                    'id', 'client_id', 'pet_id', 'schedule_id', 'starts_at', 'status_id', 'is_completed'
                ])
                ->with([
                    'client:id,name,phone',
                    'pet:id,name,breed_id',
                    'pet.breed:id,name',
                    'schedule:id,veterinarian_id,branch_id,shift_starts_at',
                    'schedule.veterinarian:id,name',
                    'schedule.branch:id,name',
                    'status:id,name,color'
                ])
                ->filter($filter);
            
            // Ограничиваем количество записей для экспорта (максимум 1000)
            $data = $query->limit(1000)->get();
            
            // Форматируем данные для экспорта
            $formattedData = [];
            foreach ($data as $visit) {
                $formattedData[] = [
                    'ID' => $visit->id,
                    'Клиент' => $visit->client ? $visit->client->name : 'Не указан',
                    'Телефон клиента' => $visit->client ? $visit->client->phone : '',
                    'Питомец' => $visit->pet ? $visit->pet->name : 'Не указан',
                    'Порода' => $visit->pet && $visit->pet->breed ? $visit->pet->breed->name : 'Не указана',
                    'Ветеринар' => $visit->schedule && $visit->schedule->veterinarian ? $visit->schedule->veterinarian->name : 'Не указан',
                    'Филиал' => $visit->schedule && $visit->schedule->branch ? $visit->schedule->branch->name : 'Не указан',
                    'Дата и время визита' => $visit->starts_at ? \Carbon\Carbon::parse($visit->starts_at)->format('d.m.Y H:i') : '',
                    'Статус' => $visit->status ? $visit->status->name : 'Не указан',
                ];
            }
            
            $filename = app(ExportService::class)->generateFilename('visits', 'xlsx');
            
            return app(ExportService::class)->toExcel($formattedData, $filename);
            
        } catch (\Exception $e) {
            Log::error('Ошибка при экспорте визитов', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return back()->withErrors(['error' => 'Ошибка при экспорте: ' . $e->getMessage()]);
        }
    }

    /**
     * Экспорт деталей визита
     */
    public function exportVisitDetails($visitId, $format = 'pdf')
    {
        try {
            $visit = Visit::with([
                'client:id,name,email,phone,address',
                'pet:id,name,breed_id,client_id,birthdate,gender,temperature,weight',
                'pet.breed:id,name,species_id',
                'pet.breed.species:id,name',
                'schedule:id,veterinarian_id,branch_id,shift_starts_at,shift_ends_at',
                'schedule.veterinarian:id,name,email,phone',
                'schedule.veterinarian.specialties:id,name',
                'schedule.branch:id,name,address,phone',
                'status:id,name,color',
                'symptoms:id,visit_id,dictionary_symptom_id,custom_symptom,notes',
                'symptoms.dictionarySymptom:id,name',
                'diagnoses:id,visit_id,dictionary_diagnosis_id,custom_diagnosis,treatment_plan',
                'diagnoses.dictionaryDiagnosis:id,name',
                'orders' => function($query) {
                    $query->select(['orders.id', 'orders.client_id', 'orders.pet_id', 'orders.branch_id', 'orders.status_id', 'orders.total', 'orders.is_paid', 'orders.closed_at', 'orders.created_at'])
                        ->with([
                            'branch:id,name,address',
                            'status:id,name,color',
                            'items:id,order_id,item_type,item_id,quantity,unit_price',
                            'items.item:id,name'
                        ]);
                }
            ])->findOrFail($visitId);

            // Форматируем данные для экспорта
            $formattedData = [
                'visit_info' => [
                    'ID визита' => $visit->id,
                    'Дата и время' => $visit->starts_at ? \Carbon\Carbon::parse($visit->starts_at)->format('d.m.Y H:i') : '',
                    'Статус' => $visit->status ? $visit->status->name : 'Не указан',
                    'Завершен' => $visit->is_completed ? 'Да' : 'Нет',
                    'Жалобы' => $visit->complaints ?: 'Не указаны',
                    'Заметки' => $visit->notes ?: 'Нет',
                    'Дата создания' => $visit->created_at ? $visit->created_at->format('d.m.Y H:i') : '',
                    'Последнее обновление' => $visit->updated_at ? $visit->updated_at->format('d.m.Y H:i') : ''
                ],
                'client_info' => [
                    'ID клиента' => $visit->client ? $visit->client->id : '',
                    'Имя' => $visit->client ? $visit->client->name : 'Не указан',
                    'Email' => $visit->client ? $visit->client->email : '',
                    'Телефон' => $visit->client ? $visit->client->phone : '',
                    'Адрес' => $visit->client ? $visit->client->address : ''
                ],
                'pet_info' => [
                    'ID питомца' => $visit->pet ? $visit->pet->id : '',
                    'Имя' => $visit->pet ? $visit->pet->name : 'Не указан',
                    'Порода' => $visit->pet && $visit->pet->breed ? $visit->pet->breed->name : 'Не указана',
                    'Вид' => $visit->pet && $visit->pet->breed && $visit->pet->breed->species ? $visit->pet->breed->species->name : 'Не указан',
                    'Пол' => $visit->pet ? $visit->pet->gender : '',
                    'Дата рождения' => $visit->pet && $visit->pet->birthdate ? $visit->pet->birthdate->format('d.m.Y') : '',
                    'Возраст' => $visit->pet && $visit->pet->birthdate ? $visit->pet->birthdate->age . ' лет' : '',
                    'Температура' => $visit->pet && $visit->pet->temperature ? $visit->pet->temperature . '°C' : 'Не измерена',
                    'Вес' => $visit->pet && $visit->pet->weight ? $visit->pet->weight . ' кг' : 'Не измерен'
                ],
                'veterinarian_info' => [
                    'ID ветеринара' => $visit->schedule && $visit->schedule->veterinarian ? $visit->schedule->veterinarian->id : '',
                    'Имя' => $visit->schedule && $visit->schedule->veterinarian ? $visit->schedule->veterinarian->name : 'Не указан',
                    'Email' => $visit->schedule && $visit->schedule->veterinarian ? $visit->schedule->veterinarian->email : '',
                    'Телефон' => $visit->schedule && $visit->schedule->veterinarian ? $visit->schedule->veterinarian->phone : '',
                    'Специализации' => $visit->schedule && $visit->schedule->veterinarian && $visit->schedule->veterinarian->specialties ? 
                        $visit->schedule->veterinarian->specialties->pluck('name')->implode(', ') : 'Не указаны'
                ],
                'branch_info' => [
                    'ID филиала' => $visit->schedule && $visit->schedule->branch ? $visit->schedule->branch->id : '',
                    'Название' => $visit->schedule && $visit->schedule->branch ? $visit->schedule->branch->name : 'Не указан',
                    'Адрес' => $visit->schedule && $visit->schedule->branch ? $visit->schedule->branch->address : '',
                    'Телефон' => $visit->schedule && $visit->schedule->branch ? $visit->schedule->branch->phone : ''
                ],
                'schedule_info' => [
                    'ID расписания' => $visit->schedule ? $visit->schedule->id : '',
                    'Начало смены' => $visit->schedule && $visit->schedule->shift_starts_at ? 
                        \Carbon\Carbon::parse($visit->schedule->shift_starts_at)->format('d.m.Y H:i') : '',
                    'Окончание смены' => $visit->schedule && $visit->schedule->shift_ends_at ? 
                        \Carbon\Carbon::parse($visit->schedule->shift_ends_at)->format('d.m.Y H:i') : ''
                ],
                'symptoms' => $visit->symptoms->map(function($symptom) {
                    return [
                        'ID симптома' => $symptom->id,
                        'Название' => $symptom->dictionarySymptom ? $symptom->dictionarySymptom->name : $symptom->custom_symptom,
                        'Тип' => $symptom->dictionarySymptom ? 'Справочный' : 'Пользовательский',
                        'Заметки' => $symptom->notes ?: 'Нет'
                    ];
                }),
                'diagnoses' => $visit->diagnoses->map(function($diagnosis) {
                    return [
                        'ID диагноза' => $diagnosis->id,
                        'Название' => $diagnosis->dictionaryDiagnosis ? $diagnosis->dictionaryDiagnosis->name : $diagnosis->custom_diagnosis,
                        'Тип' => $diagnosis->dictionaryDiagnosis ? 'Справочный' : 'Пользовательский',
                        'План лечения' => $diagnosis->treatment_plan ?: 'Не указан'
                    ];
                }),
                'orders' => $visit->orders->map(function($order) {
                    return [
                        'ID заказа' => $order->id,
                        'Филиал' => $order->branch ? $order->branch->name : 'Не указан',
                        'Адрес филиала' => $order->branch ? $order->branch->address : '',
                        'Статус' => $order->status ? $order->status->name : 'Не указан',
                        'Общая сумма' => number_format($order->total, 2, ',', ' ') . ' руб.',
                        'Оплачен' => $order->is_paid ? 'Да' : 'Нет',
                        'Дата закрытия' => $order->closed_at ? \Carbon\Carbon::parse($order->closed_at)->format('d.m.Y H:i') : 'Не закрыт',
                        'Дата создания' => $order->created_at ? $order->created_at->format('d.m.Y H:i') : '',
                        'Позиции' => $order->items ? $order->items->map(function($item) {
                            return [
                                'Тип' => class_basename($item->item_type),
                                'Название' => $item->item ? $item->item->name : 'Неизвестно',
                                'Количество' => $item->quantity,
                                'Цена за единицу' => number_format($item->unit_price, 2, ',', ' ') . ' руб.',
                                'Общая стоимость' => number_format($item->quantity * $item->unit_price, 2, ',', ' ') . ' руб.'
                            ];
                        })->toArray() : []
                    ];
                }),
                'summary' => [
                    'Количество симптомов' => $visit->symptoms->count(),
                    'Количество диагнозов' => $visit->diagnoses->count(),
                    'Количество заказов' => $visit->orders->count(),
                    'Общая сумма заказов' => number_format($visit->orders->sum('total'), 2, ',', ' ') . ' руб.',
                    'Количество позиций в заказах' => $visit->orders->sum(function($order) {
                        return $order->items ? $order->items->count() : 0;
                    })
                ]
            ];

            $filename = app(ExportService::class)->generateFilename('visit_details_' . $visit->id, $format);
            
            if ($format === 'pdf') {
                return $this->exportVisitDetailsPdf($formattedData, $filename);
            } else {
                return app(ExportService::class)->toExcel($formattedData, $filename);
            }
            
        } catch (\Exception $e) {
            Log::error('Ошибка при экспорте деталей визита', [
                'visit_id' => $visitId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return back()->withErrors(['error' => 'Ошибка при экспорте: ' . $e->getMessage()]);
        }
    }

    /**
     * Экспорт деталей визита в PDF
     */
    private function exportVisitDetailsPdf($data, $filename)
    {
        // Извлекаем отдельные переменные из массива данных
        $visitInfo = $data['visit_info'];
        $clientInfo = $data['client_info'];
        $petInfo = $data['pet_info'];
        $veterinarianInfo = $data['veterinarian_info'];
        $branchInfo = $data['branch_info'];
        $symptoms = $data['symptoms'];
        $diagnoses = $data['diagnoses'];
        $orders = $data['orders'];
        $summary = $data['summary'];
        
        $html = view('admin.exports.visit-details-pdf', compact(
            'visitInfo', 'clientInfo', 'petInfo', 'veterinarianInfo', 
            'branchInfo', 'symptoms', 'diagnoses', 'orders', 'summary'
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