<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasColumn('subject_professor', 'period_id')) {
            $periodId = \App\Models\Period::orderBy('id')->value('id');
            if ($periodId) {
                DB::table('subject_professor')->whereNull('period_id')->update(['period_id' => $periodId]);
            }

            return;
        }

        Schema::table('subject_professor', function (Blueprint $table) {
            $table->foreignId('period_id')
                ->nullable()
                ->after('professor_id')
                ->constrained('periods')
                ->cascadeOnDelete();
        });

        $periodId = \App\Models\Period::orderBy('id')->value('id');
        if ($periodId) {
            DB::table('subject_professor')->update(['period_id' => $periodId]);
        }

        Schema::table('subject_professor', function (Blueprint $table) {
            $table->foreignId('period_id')->nullable(false)->change();
        });

        Schema::table('subject_professor', function (Blueprint $table) {
            $table->dropUnique(['subject_id', 'professor_id']);
            $table->unique(['subject_id', 'professor_id', 'period_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (! Schema::hasColumn('subject_professor', 'period_id')) {
            return;
        }

        Schema::table('subject_professor', function (Blueprint $table) {
            $table->dropUnique(['subject_id', 'professor_id', 'period_id']);
            $table->unique(['subject_id', 'professor_id']);
        });
        Schema::table('subject_professor', function (Blueprint $table) {
            $table->dropForeign(['period_id']);
        });
        Schema::table('subject_professor', function (Blueprint $table) {
            $table->dropColumn('period_id');
        });
    }
};
