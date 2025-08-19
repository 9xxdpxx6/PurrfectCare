<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class TestNotifications extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'admin:test-notifications';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test notification system for admin panel';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        try {
            $this->info('Testing notification system...');
            
            // Проверяем наличие сотрудников
            $employees = \App\Models\Employee::all();
            $this->info("Found {$employees->count()} employees");
            
            if ($employees->isEmpty()) {
                $this->error('No employees found! Create at least one employee first.');
                return 1;
            }
            
            // Проверяем наличие питомцев
            $pets = \App\Models\Pet::with(['breed.species', 'client'])->limit(1)->get();
            $this->info("Found {$pets->count()} pets for testing");
            
            if ($pets->isEmpty()) {
                $this->error('No pets found! Create at least one pet first.');
                return 1;
            }
            
            // Тестируем отправку уведомления
            $pet = $pets->first();
            $this->info("Testing with pet: {$pet->name} (ID: {$pet->id})");
            
            $notificationService = new \App\Services\NotificationService();
            $notificationService->notifyAboutBotPetAdded($pet);
            
            $this->info('Notification sent successfully!');
            
            // Проверяем, что уведомление создалось
            $notifications = \App\Models\Notification::all();
            $this->info("Total notifications in database: {$notifications->count()}");
            
            if ($notifications->count() > 0) {
                $latest = $notifications->sortByDesc('created_at')->first();
                $this->info("Latest notification: {$latest->data['type']} - {$latest->data['title']}");
            }
            
            $this->info('Test completed successfully!');
            
        } catch (\Exception $e) {
            $this->error("Error: " . $e->getMessage());
            $this->error("Trace: " . $e->getTraceAsString());
            return 1;
        }
        
        return 0;
    }
}
