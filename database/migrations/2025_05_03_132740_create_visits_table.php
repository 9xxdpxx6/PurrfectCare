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
        Schema::create('visits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained('users');
            $table->foreignId('pet_id')->nullable()->constrained();
            $table->foreignId('schedule_id')->nullable()->constrained();
            $table->dateTime('starts_at');
            $table->foreignId('status_id')->constrained();
            $table->text('complaints')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->unique(['schedule_id', 'starts_at'], 'visits_schedule_starts_unique');
            
            // Индексы для оптимизации производительности
            $table->index('client_id'); // Быстрый поиск визитов клиента
            $table->index('pet_id'); // Быстрый поиск визитов питомца
            $table->index('schedule_id'); // Быстрый поиск по расписанию
            $table->index('status_id'); // Быстрый поиск по статусу
            $table->index('starts_at'); // Быстрый поиск по дате/времени
            $table->index(['client_id', 'starts_at']); // Составной индекс для поиска визитов клиента в определенный период
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('visits');
    }
};
