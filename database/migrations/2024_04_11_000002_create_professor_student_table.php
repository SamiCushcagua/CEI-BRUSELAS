<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('professor_student', function (Blueprint $table) {
            $table->id();
            $table->foreignId('professor_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('student_id')->constrained('users')->onDelete('cascade');
            $table->timestamps();

            // Asegurar que un estudiante no pueda tener la misma relación con un profesor más de una vez
            $table->unique(['professor_id', 'student_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('professor_student');
    }
}; 