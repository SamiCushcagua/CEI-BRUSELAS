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
        Schema::create('diplomas', function (Blueprint $table) {
            $table->id();
            $table->string('name_diploma');
            $table->string('description_diploma');
            $table->string('image_diploma');
            $table->foreignId('materia_id')->constrained('subjects')->onDelete('cascade');
            $table->timestamps();

            $table->unique(['name_diploma', 'materia_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('diplomas');
    }
};
