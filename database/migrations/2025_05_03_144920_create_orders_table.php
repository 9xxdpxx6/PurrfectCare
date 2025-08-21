<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('pet_id')->constrained()->onDelete('cascade');
            $table->boolean('is_paid')->default(false);
            $table->foreignId('status_id')->constrained()->onDelete('cascade');
            $table->foreignId('branch_id')->constrained()->onDelete('cascade');
            $table->foreignId('manager_id')->constrained('employees')->onDelete('cascade');
            $table->string('notes')->nullable();
            $table->decimal('total', 10, 2)->default(0);
            $table->dateTime('closed_at')->nullable();
            $table->timestamps();
            
            // Индексы для оптимизации производительности
            $table->index('client_id'); // Быстрый поиск заказов клиента
            $table->index('pet_id'); // Быстрый поиск заказов питомца
            $table->index('status_id'); // Быстрый поиск по статусу заказа
            $table->index('branch_id'); // Быстрый поиск заказов в филиале
            $table->index('manager_id'); // Быстрый поиск заказов менеджера
            $table->index('closed_at'); // Быстрый поиск по дате закрытия
            $table->index(['client_id', 'status_id']); // Составной индекс для поиска заказов клиента по статусу
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
