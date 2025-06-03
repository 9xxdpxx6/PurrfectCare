<?php

namespace Database\Seeders;
use App\Models\Branch;
use App\Models\Species;
use App\Models\Breed;
use App\Models\Supplier;
use App\Models\User;
use App\Models\Pet;
use App\Models\Specialty;
use App\Models\Employee;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        Species::factory(6)->create();

        Breed::factory(20)->create();

        User::factory(50)->create();

        Pet::factory(100)->create();

        Specialty::factory(8)->create();

        Employee::factory(30)->create()->each(function ($employee) {
            $specialties = Specialty::inRandomOrder()->limit(rand(1, 3))->pluck('id');
            $employee->specialties()->attach($specialties);
        });
        Employee::factory(30)->create()->each(function ($employee) {
            $branches = Branch::inRandomOrder()->limit(rand(1, 2))->pluck('id');
            $employee->branches()->attach($branches);
        });

        Supplier::factory(30)->create();
    }
}
