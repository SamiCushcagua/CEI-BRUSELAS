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
        Schema::create('class_attendance', function (Blueprint $table) {
            $table->id();
            $table->foreignId('professor_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('subject_id')->constrained('subjects')->onDelete('cascade');
            $table->foreignId('student_id')->constrained('users')->onDelete('cascade');
            $table->date('class_date')->comment('Fecha de la clase');
            $table->enum('attendance_status', ['present', 'absent', 'late'])->default('present')->comment('Estado de asistencia');
            $table->boolean('bible_verse_delivered')->default(false)->comment('Versículo bíblico entregado');
            $table->text('notes')->nullable()->comment('Notas adicionales');
            $table->timestamps();
            
            // Índices para optimizar consultas
            $table->index(['professor_id', 'subject_id', 'class_date']);
            $table->index(['student_id', 'class_date']);
            $table->unique(['professor_id', 'subject_id', 'student_id', 'class_date'], 'unique_attendance_per_class');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('class_attendance');
    }
};