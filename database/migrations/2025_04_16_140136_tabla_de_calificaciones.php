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

        Schema::create('calificaciones', function (Blueprint $table) {
            $table->id();
            $table->string('Examen1');
            $table->string('Examen2');
            $table->string('participacion');
            $table->string('puntualidad');
            $table->string('Material');
            $table->string('VersiculoBiblico');
            $table->string('tarea');
            $table->string('total');
            $table->timestamps();
        });
        //
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
