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
        Schema::create('notifications', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('type');
            $table->morphs('notifiable');
            $table->text('data');
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
            
            // Индексы для оптимизации производительности
            $table->index('notifiable_type'); // Быстрый поиск по типу получателя
            $table->index('notifiable_id'); // Быстрый поиск по ID получателя
            $table->index('read_at'); // Быстрый поиск прочитанных/непрочитанных уведомлений
            $table->index('type'); // Быстрый поиск по типу уведомления
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};
