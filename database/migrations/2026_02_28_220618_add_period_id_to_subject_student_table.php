<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('subject_student', function (Blueprint $table) {
            $table->foreignId('period_id')
                  ->nullable()
                  ->after('student_id')
                  ->constrained('periods')
                  ->cascadeOnDelete();
        });
 
        $periodId = \App\Models\Period::orderBy('id')->value('id');
        if ($periodId) {
            DB::table('subject_student')->update(['period_id' => $periodId]);
        }
 
        Schema::table('subject_student', function (Blueprint $table) {
            $table->foreignId('period_id')->nullable(false)->change();
        });
 
        Schema::table('subject_student', function (Blueprint $table) {
            $table->dropUnique(['subject_id', 'student_id']);
            $table->unique(['subject_id', 'student_id', 'period_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('subject_student', function (Blueprint $table) {
            $table->dropUnique(['subject_id', 'student_id', 'period_id']);
            $table->unique(['subject_id', 'student_id']);
        });
        Schema::table('subject_student', function (Blueprint $table) {
            $table->dropForeign(['period_id']);
        });
        Schema::table('subject_student', function (Blueprint $table) {
            $table->dropColumn('period_id');
        });
    }
};
