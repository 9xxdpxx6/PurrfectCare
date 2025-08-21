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
        Schema::create('units', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('symbol', 10)->unique();
            $table->timestamps();

            // Индексы для оптимизации производительности
            $table->index('name'); // Быстрый поиск единиц измерения по названию
            $table->index('symbol'); // Быстрый поиск единиц измерения по символу
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('units');
    }
};
