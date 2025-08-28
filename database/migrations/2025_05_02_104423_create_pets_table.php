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
        Schema::create('pets', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->foreignId('breed_id')->constrained()->onDelete('cascade');
            $table->date('birthdate')->nullable();
            $table->foreignId('client_id')->constrained('users')->onDelete('cascade');
            $table->decimal('temperature', 8, 2)->nullable();
            $table->decimal('weight', 8, 2)->nullable();
            $table->enum('gender', ['male', 'female', 'unknown'])->default('unknown');
            $table->timestamps();
            
            // Индексы для оптимизации производительности
            $table->index('name'); // Быстрый поиск питомцев по имени
            $table->index('client_id'); // Быстрый поиск питомцев клиента
            $table->index('breed_id'); // Быстрый поиск питомцев по породе
            $table->index('gender'); // Быстрый поиск питомцев по полу
            $table->index('birthdate'); // Быстрый поиск питомцев по дате рождения
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pets');
    }
};
