<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use App\Models\Specialty;
use App\Models\Branch;
/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Employee>
 */
class EmployeeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->firstName . ' ' . $this->faker->lastName,
            'email' => $this->faker->unique()->safeEmail,
            'phone' => '+7' . $this->faker->numerify('##########'),  
            'password' => Hash::make('password'),
            'is_active' => $this->faker->boolean(90), // 90% активных сотрудников
            'created_at' => $this->faker->dateTimeBetween('-2 years', 'now')->format('Y-m-d H:i:s'),
            'updated_at' => $this->faker->dateTimeBetween('-2 years', 'now')->format('Y-m-d H:i:s'),
        ];
    }

    /**
     * Configure the model factory.
     */
    public function configure()
    {
        return $this->afterCreating(function ($employee) {
            // Получаем случайную специальность
            $specialty = Specialty::inRandomOrder()->first();
            
            if ($specialty) {
                // Привязываем специальность к сотруднику
                $employee->specialties()->attach($specialty->id);
                
                // Назначаем роль в зависимости от специальности
                $this->assignRoleBySpecialty($employee, $specialty);
            }
            
            // Привязываем к случайной филиалу
            $branch = Branch::inRandomOrder()->first();
            if ($branch) {
                $employee->branches()->attach($branch->id);
            }
        });
    }

    /**
     * Назначить роль в зависимости от специальности
     */
    private function assignRoleBySpecialty($employee, $specialty)
    {
        $roleName = $this->getRoleBySpecialty($specialty);
        
        if ($roleName) {
            $employee->assignRole($roleName);
        }
    }

    /**
     * Получить роль по специальности
     */
    private function getRoleBySpecialty($specialty)
    {
        // Врачебные специальности получают роль veterinarian
        if ($specialty->is_veterinarian) {
            return 'veterinarian';
        }

        // Не врачебные специальности получают роли в зависимости от названия
        $specialtyName = strtolower($specialty->name);
        
        if (str_contains($specialtyName, 'управляющий') || str_contains($specialtyName, 'администратор')) {
            return 'manager';
        }
        
        if (str_contains($specialtyName, 'менеджер') || str_contains($specialtyName, 'закупок')) {
            return 'manager';
        }
        
        if (str_contains($specialtyName, 'лаборант') || str_contains($specialtyName, 'фельдшер')) {
            return 'veterinarian'; // Могут работать с анализами
        }
        
        if (str_contains($specialtyName, 'ассистент') || str_contains($specialtyName, 'стажёр')) {
            return 'veterinarian'; // Помогают врачам
        }
        
        // По умолчанию для остальных специальностей
        return 'veterinarian';
    }
}
