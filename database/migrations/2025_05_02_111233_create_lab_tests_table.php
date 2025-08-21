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
        Schema::create('lab_tests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pet_id')->constrained()->onDelete('cascade');
            $table->foreignId('lab_test_type_id')->constrained()->onDelete('cascade');
            $table->foreignId('veterinarian_id')->constrained('employees')->onDelete('cascade');
            $table->dateTime('received_at');
            $table->dateTime('completed_at')->nullable();
            $table->timestamps();
            
            // Индексы для оптимизации производительности
            $table->index('pet_id'); // Быстрый поиск тестов питомца
            $table->index('veterinarian_id'); // Быстрый поиск тестов ветеринара
            $table->index('lab_test_type_id'); // Быстрый поиск по типу теста
            $table->index('received_at'); // Быстрый поиск по дате получения
            $table->index('completed_at'); // Быстрый поиск завершенных тестов
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lab_tests');
    }
};
