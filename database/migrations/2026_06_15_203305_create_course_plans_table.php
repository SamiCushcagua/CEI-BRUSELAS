<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('course_plans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subject_id')->constrained()->cascadeOnDelete();
            $table->foreignId('period_id')->constrained()->cascadeOnDelete();
            $table->string('textbook')->nullable();
            $table->json('cognitive_objectives')->nullable();
            $table->json('affective_objectives')->nullable();
            $table->json('psychomotor_objectives')->nullable();
            $table->json('requirements')->nullable();
            $table->json('task_descriptions')->nullable();
            $table->json('general_notes')->nullable();
            $table->text('complementary_data')->nullable();
            $table->json('bibliography')->nullable();
            $table->string('status')->default('draft');
            $table->foreignId('last_edited_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['subject_id', 'period_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('course_plans');
    }
};
