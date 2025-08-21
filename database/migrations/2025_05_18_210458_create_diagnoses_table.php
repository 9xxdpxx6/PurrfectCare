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
        Schema::create('diagnoses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('visit_id')->constrained()->onDelete('cascade');
            $table->foreignId('dictionary_diagnosis_id')->nullable()->constrained()->onDelete('cascade');
            $table->string('custom_diagnosis')->nullable();
            $table->text('treatment_plan')->nullable();
            $table->timestamps();

            // Индексы для оптимизации производительности
            $table->index('visit_id'); // Быстрый поиск диагнозов по приёму
            $table->index('dictionary_diagnosis_id'); // Быстрый поиск диагнозов по словарю
            $table->index('custom_diagnosis'); // Быстрый поиск диагнозов по пользовательскому диагнозу
            // Удалён индекс по treatment_plan: TEXT не индексируется без длины; для MySQL нельзя
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('diagnoses');
    }
};
