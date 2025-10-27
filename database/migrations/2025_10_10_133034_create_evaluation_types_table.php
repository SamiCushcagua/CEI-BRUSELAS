<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('evaluation_types', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100)->comment('Nombre del tipo de evaluación');
            $table->text('description')->nullable()->comment('Descripción del tipo de evaluación');
            $table->boolean('is_active')->default(true)->comment('Estado activo/inactivo');
            $table->timestamps();
            
            $table->index('is_active');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('evaluation_types');
    }
};