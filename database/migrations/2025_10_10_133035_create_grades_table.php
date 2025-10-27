<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('grades', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('subject_id')->constrained('subjects')->onDelete('cascade');
            $table->integer('trimester')->comment('Trimestre (1, 2, 3)');
            $table->integer('year')->comment('Año académico');
            $table->decimal('task_score', 5, 2)->nullable()->comment('Calificación de tareas');
            $table->decimal('exam_score1', 5, 2)->nullable()->comment('Calificación de examen 1');
            $table->decimal('exam_score2', 5, 2)->nullable()->comment('Calificación de examen 2');
            $table->decimal('participation_score', 5, 2)->nullable()->comment('Calificación de participación');
            $table->decimal('bible_score', 5, 2)->nullable()->comment('Calificación de Biblia');
            $table->decimal('text_score', 5, 2)->nullable()->comment('Calificación de texto');
            $table->decimal('other_score', 5, 2)->nullable()->comment('Otra calificación');
            $table->text('notes')->nullable()->comment('Notas adicionales');
            $table->timestamps();
            
            // Índices para optimizar consultas
            $table->index(['student_id', 'subject_id', 'trimester', 'year']);
            $table->unique(['student_id', 'subject_id', 'trimester', 'year'], 'unique_grade_per_trimester');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('grades');
    }
};