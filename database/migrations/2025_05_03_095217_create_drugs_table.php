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
        Schema::create('drugs', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->decimal('price', 10, 2);
            $table->boolean('prescription_required')->default(false);
            $table->foreignId('unit_id')->nullable()->constrained()->onDelete('cascade');
            $table->timestamps();

            // Индексы для оптимизации производительности
            $table->index('name'); // Быстрый поиск препаратов по названию
            $table->index('unit_id'); // Быстрый поиск препаратов по единице измерения
            $table->index('prescription_required'); // Быстрый поиск препаратов по наличию рецепта
        });

        // Создаем таблицу branch_drug для управления запасами по филиалам
        Schema::create('branch_drug', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->constrained()->onDelete('cascade');
            $table->foreignId('drug_id')->constrained()->onDelete('cascade');
            $table->integer('quantity')->default(0);
            $table->timestamps();

            // Unique constraint на комбинацию branch_id и drug_id
            $table->unique(['branch_id', 'drug_id']);

            // Индексы для производительности
            $table->index('branch_id');
            $table->index('drug_id');
            $table->index('quantity');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('branch_drug');
        Schema::dropIfExists('drugs');
    }
};
