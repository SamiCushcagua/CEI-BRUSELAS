<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('subject_student', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subject_id')->constrained()->onDelete('cascade');
            $table->foreignId('student_id')->constrained('users')->onDelete('cascade');
            $table->timestamps();

            // Asegurar que un estudiante no pueda estar inscrito dos veces en la misma materia
            $table->unique(['subject_id', 'student_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('subject_student');
    }
}; 