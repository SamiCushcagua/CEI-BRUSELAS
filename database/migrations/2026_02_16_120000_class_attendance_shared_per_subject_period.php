<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use App\Models\Period;

return new class extends Migration
{
    /**
     * Asistencia compartida: una fila por materia + alumno + fecha + periodo.
     * professor_id = último que guardó (auditoría), ya no parte de la unicidad.
     */
    public function up(): void
    {
        Schema::table('class_attendance', function (Blueprint $table) {
            $table->foreignId('period_id')->nullable()->after('student_id')->constrained('periods')->cascadeOnDelete();
        });

        $this->backfillPeriodIds();

        // Fusionar duplicados (mismo subject, student, fecha, periodo) — conservar el id más alto
        $dupes = DB::table('class_attendance')
            ->select('subject_id', 'student_id', 'class_date', 'period_id', DB::raw('MAX(id) as keep_id'), DB::raw('COUNT(*) as c'))
            ->groupBy('subject_id', 'student_id', 'class_date', 'period_id')
            ->having('c', '>', 1)
            ->get();

        foreach ($dupes as $row) {
            DB::table('class_attendance')
                ->where('subject_id', $row->subject_id)
                ->where('student_id', $row->student_id)
                ->where('class_date', $row->class_date)
                ->where('period_id', $row->period_id)
                ->where('id', '<', $row->keep_id)
                ->delete();
        }

        Schema::table('class_attendance', function (Blueprint $table) {
            $table->dropUnique('unique_attendance_per_class');
        });

        // period_id obligatorio tras backfill (evitar ->change() en FK sin dbal)
        DB::statement('ALTER TABLE `class_attendance` MODIFY `period_id` BIGINT UNSIGNED NOT NULL');

        Schema::table('class_attendance', function (Blueprint $table) {
            $table->unique(['subject_id', 'student_id', 'class_date', 'period_id'], 'class_attendance_subject_student_date_period_unique');
        });

        // professor_id opcional (auditoría)
        Schema::table('class_attendance', function (Blueprint $table) {
            $table->dropForeign(['professor_id']);
        });

        DB::statement('ALTER TABLE `class_attendance` MODIFY `professor_id` BIGINT UNSIGNED NULL');

        Schema::table('class_attendance', function (Blueprint $table) {
            $table->foreign('professor_id')->references('id')->on('users')->nullOnDelete();
        });
    }

    private function backfillPeriodIds(): void
    {
        $fallbackPeriodId = Period::orderBy('id')->value('id');
        if (!$fallbackPeriodId) {
            throw new \RuntimeException('No hay periodos en la tabla periods. Crea al menos uno antes de migrar.');
        }

        $rows = DB::table('class_attendance')->select('id', 'class_date')->get();

        foreach ($rows as $row) {
            $date = \Carbon\Carbon::parse($row->class_date);
            $year = (int) $date->year;
            $month = (int) $date->month;
            $trimester = $this->trimesterFromMonth($month);

            $periodId = Period::where('year', $year)->where('trimester', $trimester)->value('id')
                ?? $fallbackPeriodId;

            DB::table('class_attendance')->where('id', $row->id)->update(['period_id' => $periodId]);
        }
    }

    private function trimesterFromMonth(int $month): int
    {
        if ($month >= 1 && $month <= 4) {
            return 1;
        }
        if ($month >= 5 && $month <= 8) {
            return 2;
        }

        return 3;
    }

    public function down(): void
    {
        Schema::table('class_attendance', function (Blueprint $table) {
            $table->dropUnique('class_attendance_subject_student_date_period_unique');
        });

        Schema::table('class_attendance', function (Blueprint $table) {
            $table->dropForeign(['professor_id']);
        });

        DB::statement('ALTER TABLE `class_attendance` MODIFY `professor_id` BIGINT UNSIGNED NOT NULL');

        Schema::table('class_attendance', function (Blueprint $table) {
            $table->foreign('professor_id')->references('id')->on('users')->onDelete('cascade');
        });

        Schema::table('class_attendance', function (Blueprint $table) {
            $table->dropForeign(['period_id']);
            $table->dropColumn('period_id');
        });

        Schema::table('class_attendance', function (Blueprint $table) {
            $table->unique(['professor_id', 'subject_id', 'student_id', 'class_date'], 'unique_attendance_per_class');
        });
    }
};
