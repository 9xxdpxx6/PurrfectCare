<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class PermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $modules = [
            'main', 'orders', 'services', 'statistics_general', 'statistics_finance',
            'statistics_efficiency', 'statistics_clients', 'statistics_medicine',
            'statistics_conversion', 'clients', 'pets', 'visits', 'vaccinations',
            'lab_tests', 'drugs', 'employees', 'roles', 'schedules', 'deliveries',
            'notifications', 'settings_analysis_types', 'settings_analysis_parameters',
            'settings_vaccination_types', 'settings_statuses', 'settings_units',
            'settings_branches', 'settings_specialties', 'settings_animal_types',
            'settings_breeds', 'settings_suppliers', 'settings_diagnoses',
            'settings_symptoms',
        ];

        $crudOperations = ['create', 'read', 'update', 'delete', 'export'];
        $permissions = [];

        foreach ($modules as $module) {
            if ($module === 'main') {
                $permissions[] = Permission::firstOrCreate(['name' => 'main.read', 'guard_name' => 'admin']);
                $permissions[] = Permission::firstOrCreate(['name' => 'main.read', 'guard_name' => 'web']);
                continue;
            }
            foreach ($crudOperations as $operation) {
                // Permissions for admin guard (Employee model)
                $permissions[] = Permission::firstOrCreate(['name' => "$module.$operation", 'guard_name' => 'admin']);
                // Permissions for web guard (User model) - mostly for read access, if applicable
                // We'll create read and export permissions for modules that might be relevant to clients
                if (in_array($operation, ['read', 'export']) && in_array($module, ['orders', 'pets', 'visits'])) {
                    $permissions[] = Permission::firstOrCreate(['name' => "$module.$operation", 'guard_name' => 'web']);
                }
            }
        }

        // Create Roles
        $superAdminRole = Role::firstOrCreate(['name' => 'super-admin', 'guard_name' => 'admin']);
        $adminRole = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'admin']);
        $managerRole = Role::firstOrCreate(['name' => 'manager', 'guard_name' => 'admin']);
        $veterinarianRole = Role::firstOrCreate(['name' => 'veterinarian', 'guard_name' => 'admin']);
        $accountantRole = Role::firstOrCreate(['name' => 'accountant', 'guard_name' => 'admin']);
        $clientRole = Role::firstOrCreate(['name' => 'client', 'guard_name' => 'web']);


        // Assign all permissions to super-admin
        $superAdminRole->givePermissionTo(Permission::where('guard_name', 'admin')->get());

        // Assign permissions to admin
        $adminRole->givePermissionTo(Permission::where('guard_name', 'admin')
            ->whereNotIn('name', [
                'employees.create', 'employees.update', 'employees.delete',
                'roles.create', 'roles.update', 'roles.delete',
            ])->get());


        // Assign permissions to manager
        $managerRole->givePermissionTo([
            'main.read',
            'notifications.read', 'notifications.update',
            'orders.create', 'orders.read', 'orders.update', 'orders.export',
            'visits.create', 'visits.read', 'visits.update', 'visits.export',
            'clients.create', 'clients.read', 'clients.update', 'clients.export',
            'pets.create', 'pets.read', 'pets.update', 'pets.export',
            'schedules.read', 'schedules.export',
            'deliveries.create', 'deliveries.read', 'deliveries.update', 'deliveries.export',
            'statistics_general.read', 'statistics_general.export',
            'statistics_efficiency.read', 'statistics_efficiency.export',
            'statistics_clients.read', 'statistics_clients.export',
        ]);

        // Assign permissions to veterinarian
        $veterinarianRole->givePermissionTo([
            'main.read',
            'notifications.read', 'notifications.update',
            'visits.create', 'visits.read', 'visits.update', 'visits.export',
            'vaccinations.create', 'vaccinations.read', 'vaccinations.update', 'vaccinations.export',
            'lab_tests.create', 'lab_tests.read', 'lab_tests.update', 'lab_tests.export',
            'schedules.read', 'schedules.export',
            'pets.create', 'pets.read', 'pets.update', 'pets.export',
            'orders.read', 'orders.export', // Может просматривать заказы для своих визитов
            'clients.read', 'clients.export', // Может просматривать данные клиентов
            'drugs.read', 'drugs.export', // Может просматривать лекарства
        ]);

        // Assign permissions to accountant
        $accountantRole->givePermissionTo([
            'main.read',
            'notifications.read', 'notifications.update',
            'orders.read', 'orders.export',
            'visits.read', 'visits.export',
            'clients.read', 'clients.export',
            'pets.read', 'pets.export',
            'statistics_finance.read', 'statistics_finance.export',
            'statistics_general.read', 'statistics_general.export',
            'statistics_efficiency.read', 'statistics_efficiency.export',
            'statistics_clients.read', 'statistics_clients.export',
            'statistics_conversion.read', 'statistics_conversion.export',
            'drugs.read', 'drugs.export', // Для учета расходов на лекарства
            'services.read', 'services.export', // Для учета доходов от услуг
        ]);

        // Assign permissions to client (web guard)
        $clientRole->givePermissionTo([
            Permission::firstOrCreate(['name' => 'main.read', 'guard_name' => 'web']),
            Permission::firstOrCreate(['name' => 'orders.read', 'guard_name' => 'web']),
            Permission::firstOrCreate(['name' => 'orders.export', 'guard_name' => 'web']),
            Permission::firstOrCreate(['name' => 'pets.read', 'guard_name' => 'web']),
            Permission::firstOrCreate(['name' => 'pets.export', 'guard_name' => 'web']),
            Permission::firstOrCreate(['name' => 'visits.read', 'guard_name' => 'web']),
            Permission::firstOrCreate(['name' => 'visits.export', 'guard_name' => 'web']),
        ]);

        // Create a Super Admin Employee
        $superAdminEmployee = Employee::firstOrCreate(
            ['email' => 'superadmin@example.com'],
            [
                'name' => 'Super Admin',
                'phone' => '1234567890',
                'password' => Hash::make('password'),
                'is_active' => true,
            ]
        );
        $superAdminEmployee->assignRole('super-admin');

        // Create a normal Admin Employee
        $adminEmployee = Employee::firstOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Admin User',
                'phone' => '0987654321',
                'password' => Hash::make('password'),
                'is_active' => true,
            ]
        );
        $adminEmployee->assignRole('admin');

        // Create a Manager Employee
        $managerEmployee = Employee::firstOrCreate(
            ['email' => 'manager@example.com'],
            [
                'name' => 'Manager User',
                'phone' => '1122334455',
                'password' => Hash::make('password'),
                'is_active' => true,
            ]
        );
        $managerEmployee->assignRole('manager');

        // Create a Veterinarian Employee
        $veterinarianEmployee = Employee::firstOrCreate(
            ['email' => 'veterinarian@example.com'],
            [
                'name' => 'Veterinarian User',
                'phone' => '2233445566',
                'password' => Hash::make('password'),
                'is_active' => true,
            ]
        );
        $veterinarianEmployee->assignRole('veterinarian');

        // Create an Accountant Employee
        $accountantEmployee = Employee::firstOrCreate(
            ['email' => 'accountant@example.com'],
            [
                'name' => 'Accountant User',
                'phone' => '3344556677',
                'password' => Hash::make('password'),
                'is_active' => true,
            ]
        );
        $accountantEmployee->assignRole('accountant');

        // Create a normal User (Client)
        $clientUser = User::firstOrCreate(
            ['email' => 'client@example.com'],
            [
                'name' => 'Client User',
                'phone' => '4455667788',
                'password' => Hash::make('password'),
            ]
        );
        $clientUser->assignRole('client');
    }
}
