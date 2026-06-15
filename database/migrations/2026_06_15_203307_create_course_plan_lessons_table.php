<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('course_plan_lessons', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_plan_id')->constrained()->cascadeOnDelete();
            $table->unsignedSmallInteger('class_number');
            $table->date('class_date');
            $table->string('topic')->nullable();
            $table->text('assignment')->nullable();
            $table->boolean('has_homework')->default(false);
            $table->timestamps();

            $table->unique(['course_plan_id', 'class_number']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('course_plan_lessons');
    }
};
