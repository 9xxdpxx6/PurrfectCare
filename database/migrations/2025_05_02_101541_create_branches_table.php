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
        Schema::create('branches', function ($table) {
            $table->id();
            $table->string('name');
            $table->string('address');
            $table->string('phone');
            $table->time('opens_at');
            $table->time('closes_at');
            $table->timestamps();

            // Индексы для оптимизации производительности
            $table->index('name'); // Быстрый поиск филиалов по названию
            $table->index('phone'); // Быстрый поиск филиалов по телефону
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('branches');
    }
};
