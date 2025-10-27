<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('grade_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subject_id')->constrained('subjects')->onDelete('cascade');
            $table->foreignId('evaluation_type_id')->constrained('evaluation_types')->onDelete('cascade');
            $table->decimal('weight', 5, 2)->comment('Peso de la evaluación (porcentaje)');
            $table->decimal('max_score', 5, 2)->comment('Puntuación máxima');
            $table->decimal('passing_score', 5, 2)->comment('Puntuación mínima para aprobar');
            $table->timestamps();
            
            // Índices
            $table->index(['subject_id', 'evaluation_type_id']);
            $table->unique(['subject_id', 'evaluation_type_id'], 'unique_subject_evaluation');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('grade_settings');
    }
};