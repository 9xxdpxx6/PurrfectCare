<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class CreateTestEmployee extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'admin:create-employee {email} {password} {name}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create an employee account for admin panel';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $email = $this->argument('email');
        $password = $this->argument('password');
        $name = $this->argument('name');

        try {
            $employee = \App\Models\Employee::create([
                'name' => $name,
                'email' => $email,
                'password' => bcrypt($password),
                'phone' => '+7 (999) 999-99-99',
                'is_active' => true,
            ]);

            $this->info("Employee created successfully!");
            $this->info("Email: {$employee->email}");
            $this->info("Name: {$employee->name}");
            $this->info("Password: {$password}");
            
        } catch (\Exception $e) {
            $this->error("Error creating employee: " . $e->getMessage());
        }
    }
}
