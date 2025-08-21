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
        Schema::create('breeds', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->foreignId('species_id')->constrained()->onDelete('cascade');
            $table->timestamps();

            // Индексы для оптимизации производительности
            $table->index('name'); // Быстрый поиск пород по названию
            $table->index('species_id'); // Быстрый поиск пород по виду
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('breeds');
    }
};
