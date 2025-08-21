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
        Schema::create('symptoms', function (Blueprint $table) {
            $table->id();
            $table->foreignId('visit_id')->constrained()->onDelete('cascade');
            $table->foreignId('dictionary_symptom_id')->nullable()->constrained()->onDelete('cascade');
            $table->string('custom_symptom')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            // Индексы для оптимизации производительности
            $table->index('visit_id'); // Быстрый поиск симптомов по приёму
            $table->index('dictionary_symptom_id'); // Быстрый поиск симптомов по словарю
            $table->index('custom_symptom'); // Быстрый поиск симптомов по пользовательскому симптому
            // Удалён индекс по notes: TEXT не индексируется без длины; для MySQL нельзя
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('symptoms');
    }
};
