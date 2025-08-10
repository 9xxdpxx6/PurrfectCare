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
        Schema::create('vaccination_type_drugs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vaccination_type_id')->constrained()->onDelete('cascade');
            $table->foreignId('drug_id')->constrained()->onDelete('cascade');
            $table->decimal('dosage', 5, 2);
            $table->string('batch_template')->nullable()->comment('Шаблон серии препарата');
            $table->timestamps();

            $table->unique(['vaccination_type_id', 'drug_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vaccination_type_drugs');
    }
};