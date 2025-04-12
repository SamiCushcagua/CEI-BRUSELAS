<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('subject_professor', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subject_id')->constrained()->onDelete('cascade');
            $table->foreignId('professor_id')->constrained('users')->onDelete('cascade');
            $table->timestamps();

            // Asegurar que un profesor no pueda estar asignado dos veces a la misma materia
            $table->unique(['subject_id', 'professor_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('subject_professor');
    }
}; 