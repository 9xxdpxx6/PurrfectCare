<?php

namespace App\Http\Filters;

use Illuminate\Database\Eloquent\Builder;

class RoleSearchFilter
{
    protected $moduleTranslations = [
        'main' => 'Главная',
        'orders' => 'Заказы',
        'services' => 'Услуги',
        'statistics_general' => 'Общая статистика',
        'statistics_finance' => 'Финансовая статистика',
        'statistics_efficiency' => 'Статистика эффективности',
        'statistics_clients' => 'Статистика клиентов',
        'statistics_medicine' => 'Медицинская статистика',
        'statistics_conversion' => 'Статистика конверсии',
        'clients' => 'Клиенты',
        'pets' => 'Питомцы',
        'visits' => 'Приёмы',
        'vaccinations' => 'Вакцинации',
        'lab_tests' => 'Анализы',
        'drugs' => 'Препараты',
        'employees' => 'Сотрудники',
        'roles' => 'Роли',
        'schedules' => 'Расписания',
        'deliveries' => 'Поставки',
        'settings_analysis_types' => 'Типы анализов',
        'settings_analysis_parameters' => 'Параметры анализов',
        'settings_vaccination_types' => 'Типы вакцинаций',
        'settings_statuses' => 'Статусы',
        'settings_units' => 'Единицы измерения',
        'settings_branches' => 'Филиалы',
        'settings_specialties' => 'Специальности',
        'settings_animal_types' => 'Виды животных',
        'settings_breeds' => 'Породы животных',
        'settings_suppliers' => 'Поставщики',
        'settings_diagnoses' => 'Диагнозы (словарь)',
        'settings_symptoms' => 'Симптомы (словарь)',
    ];

    public function apply(Builder $query, string $search): Builder
    {
        if (empty($search)) {
            return $query;
        }

        // Разбиваем поисковый запрос на отдельные слова
        $keywords = array_filter(explode(' ', trim($search)));
        
        if (empty($keywords)) {
            return $query;
        }

        return $query->where(function ($q) use ($keywords) {
            foreach ($keywords as $keyword) {
                $q->where(function ($subQuery) use ($keyword) {
                    // Поиск по названию роли
                    $subQuery->where('name', 'LIKE', "%{$keyword}%")
                        // Поиск по модулям через permissions
                        ->orWhereHas('permissions', function ($permissionQuery) use ($keyword) {
                            $permissionQuery->where(function ($permSubQuery) use ($keyword) {
                                // Поиск по английскому названию модуля
                                $permSubQuery->where('name', 'LIKE', "%{$keyword}%")
                                    // Поиск по русскому названию модуля
                                    ->orWhere(function ($russianQuery) use ($keyword) {
                                        foreach ($this->moduleTranslations as $module => $translation) {
                                            if (stripos($translation, $keyword) !== false) {
                                                $russianQuery->orWhere('name', 'LIKE', "{$module}.%");
                                            }
                                        }
                                    });
                            });
                        });
                });
            }
        });
    }
}
