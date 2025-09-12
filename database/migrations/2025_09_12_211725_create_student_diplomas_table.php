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
        Schema::create('student_diplomas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('diploma_id')->constrained('diplomas')->onDelete('cascade');
            $table->string('fecha_obtencion');
            $table->string('calificacion');
            $table->string('nombre_diploma');
            $table->enum('estado', ['pendiente', 'entregado', 'rechazado']);
            $table->timestamps();

            $table->unique(['student_id', 'diploma_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('student_diplomas');
    }
};
