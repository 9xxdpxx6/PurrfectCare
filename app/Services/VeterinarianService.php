<?php

namespace App\Services;

use App\Models\Employee;
use App\Models\Branch;
use Illuminate\Database\Eloquent\Collection;

class VeterinarianService
{
    /**
     * Получить ветеринаров для филиала с поиском
     */
    public function getVeterinariansForBranch(int $branchId, ?string $search = null): Collection
    {
        $query = Employee::select('employees.id', 'employees.name')
            ->where('employees.is_active', true)
            ->whereHas('specialties', function ($query) {
                $query->where('is_veterinarian', true);
            })
            ->whereHas('schedules', function ($query) use ($branchId) {
                $query->where('schedules.branch_id', $branchId)
                      ->where('schedules.shift_starts_at', '>=', now())
                      ->where('schedules.shift_starts_at', '<=', now()->addDays(30));
            })
            ->with(['specialties' => function ($query) {
                $query->select('specialties.id', 'specialties.name', 'specialties.is_veterinarian')
                    ->where('specialties.is_veterinarian', true);
            }])
            ->orderBy('employees.name');

        // Поиск по ФИО или специальности
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('employees.name', 'like', "%{$search}%")
                  ->orWhereHas('specialties', function ($subQuery) use ($search) {
                      $subQuery->where('specialties.name', 'like', "%{$search}%")
                               ->where('specialties.is_veterinarian', true);
                  });
            });
        }

        return $query->get();
    }

    /**
     * Получить всех активных ветеринаров с поиском
     */
    public function getAllVeterinarians(?string $search = null, int $limit = 6): Collection
    {
        $query = Employee::select('employees.id', 'employees.name')
            ->where('employees.is_active', true)
            ->whereHas('specialties', function ($query) {
                $query->where('is_veterinarian', true);
            })
            ->with(['specialties' => function ($query) {
                $query->select('specialties.id', 'specialties.name', 'specialties.is_veterinarian')
                    ->where('specialties.is_veterinarian', true);
            }])
            ->orderBy('employees.name');

        // Поиск по ФИО или специальности
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('employees.name', 'like', "%{$search}%")
                  ->orWhereHas('specialties', function ($subQuery) use ($search) {
                      $subQuery->where('specialties.name', 'like', "%{$search}%")
                               ->where('specialties.is_veterinarian', true);
                  });
            });
        }

        return $query->limit($limit)->get();
    }

    /**
     * Получить ветеринара по ID
     */
    public function getVeterinarianById(int $id): ?Employee
    {
        return Employee::select('employees.id', 'employees.name')
            ->where('employees.is_active', true)
            ->whereHas('specialties', function ($query) {
                $query->where('is_veterinarian', true);
            })
            ->with(['specialties' => function ($query) {
                $query->select('specialties.id', 'specialties.name', 'specialties.is_veterinarian')
                    ->where('specialties.is_veterinarian', true);
            }])
            ->find($id);
    }
}
