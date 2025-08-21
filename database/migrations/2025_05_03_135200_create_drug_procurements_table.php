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
        Schema::create('drug_procurements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('supplier_id')->constrained()->onDelete('cascade');
            $table->foreignId('drug_id')->constrained()->onDelete('cascade');
            $table->date('delivery_date');
            $table->date('expiry_date');
            $table->date('manufacture_date');
            $table->date('packaging_date')->nullable();
            $table->decimal('price', 10, 2);
            $table->integer('quantity');
            $table->timestamps();
            
            // Индексы для оптимизации производительности
            $table->index('supplier_id'); // Быстрый поиск закупок поставщика
            $table->index('drug_id'); // Быстрый поиск закупок лекарства
            $table->index('delivery_date'); // Быстрый поиск по дате доставки
            $table->index('expiry_date'); // Быстрый поиск по сроку годности
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('drug_procurements');
    }
};
