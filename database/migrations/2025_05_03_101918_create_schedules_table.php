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
        Schema::create('schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('veterinarian_id')->constrained('employees');
            $table->foreignId('branch_id')->constrained();
            $table->dateTime('shift_starts_at');
            $table->dateTime('shift_ends_at');
            $table->timestamps();
            
            // Индексы для оптимизации производительности
            $table->index('veterinarian_id'); // Быстрый поиск расписания ветеринара
            $table->index('branch_id'); // Быстрый поиск расписания в филиале
            $table->index('shift_starts_at'); // Быстрый поиск по времени начала смены
            $table->index('shift_ends_at'); // Быстрый поиск по времени окончания смены
            $table->index(['branch_id', 'shift_starts_at']); // Составной индекс для поиска доступного времени в филиале
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('schedules');
    }
};
