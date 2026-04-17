<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Asistencia compartida: una fila por materia + alumno + fecha + periodo.
     * professor_id = último que guardó (auditoría), ya no parte de la unicidad.
     */
    public function up(): void
    {
        if (! Schema::hasTable('class_attendance')) {
            return;
        }

        $this->ensurePeriodsTableExists();
        $fallbackPeriodId = $this->ensureAtLeastOnePeriodExists();

        if (! Schema::hasColumn('class_attendance', 'period_id')) {
            Schema::table('class_attendance', function (Blueprint $table) {
                $table->foreignId('period_id')
                    ->nullable()
                    ->after('student_id')
                    ->constrained('periods')
                    ->cascadeOnDelete();
            });
        }

        $this->backfillPeriodIds($fallbackPeriodId);

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

        if ($this->indexExists('class_attendance', 'unique_attendance_per_class')) {
            Schema::table('class_attendance', function (Blueprint $table) {
                $table->dropUnique('unique_attendance_per_class');
            });
        }

        // period_id obligatorio tras backfill (evitar ->change() en FK sin dbal)
        if (Schema::hasColumn('class_attendance', 'period_id')) {
            DB::statement('ALTER TABLE `class_attendance` MODIFY `period_id` BIGINT UNSIGNED NOT NULL');
        }

        if (! $this->indexExists('class_attendance', 'class_attendance_subject_student_date_period_unique')) {
            Schema::table('class_attendance', function (Blueprint $table) {
                $table->unique(
                    ['subject_id', 'student_id', 'class_date', 'period_id'],
                    'class_attendance_subject_student_date_period_unique'
                );
            });
        }

        // professor_id opcional (auditoría)
        if ($this->foreignKeyExistsForColumn('class_attendance', 'professor_id')) {
            Schema::table('class_attendance', function (Blueprint $table) {
                $table->dropForeign(['professor_id']);
            });
        }

        if (Schema::hasColumn('class_attendance', 'professor_id')) {
            DB::statement('ALTER TABLE `class_attendance` MODIFY `professor_id` BIGINT UNSIGNED NULL');
        }

        if (! $this->foreignKeyExistsForColumn('class_attendance', 'professor_id')) {
            Schema::table('class_attendance', function (Blueprint $table) {
                $table->foreign('professor_id')->references('id')->on('users')->nullOnDelete();
            });
        }
    }

    private function backfillPeriodIds(int $fallbackPeriodId): void
    {
        $rows = DB::table('class_attendance')
            ->select('id', 'class_date')
            ->whereNull('period_id')
            ->get();

        foreach ($rows as $row) {
            $date = \Carbon\Carbon::parse($row->class_date);
            $year = (int) $date->year;
            $month = (int) $date->month;
            $trimester = $this->trimesterFromMonth($month);

            $periodId = DB::table('periods')
                ->where('year', $year)
                ->where('trimester', $trimester)
                ->value('id')
                ?? $fallbackPeriodId;

            DB::table('class_attendance')->where('id', $row->id)->update(['period_id' => $periodId]);
        }
    }

    private function ensurePeriodsTableExists(): void
    {
        if (Schema::hasTable('periods')) {
            return;
        }

        Schema::create('periods', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->integer('year');
            $table->integer('trimester');
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->boolean('is_active')->default(false);
            $table->boolean('is_locked')->default(false);
            $table->timestamps();
            $table->unique(['year', 'trimester']);
        });
    }

    private function ensureAtLeastOnePeriodExists(): int
    {
        $existingPeriodId = DB::table('periods')->orderBy('id')->value('id');
        if ($existingPeriodId) {
            return (int) $existingPeriodId;
        }

        $now = \Carbon\Carbon::now();
        $year = (int) $now->year;
        $trimester = $this->trimesterFromMonth((int) $now->month);

        return (int) DB::table('periods')->insertGetId([
            'name' => sprintf('Trimestre %d - %d', $trimester, $year),
            'year' => $year,
            'trimester' => $trimester,
            'start_date' => null,
            'end_date' => null,
            'is_active' => true,
            'is_locked' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function indexExists(string $table, string $indexName): bool
    {
        return DB::table('information_schema.statistics')
            ->where('table_schema', DB::getDatabaseName())
            ->where('table_name', $table)
            ->where('index_name', $indexName)
            ->exists();
    }

    private function foreignKeyExistsForColumn(string $table, string $column): bool
    {
        return DB::table('information_schema.key_column_usage')
            ->where('table_schema', DB::getDatabaseName())
            ->where('table_name', $table)
            ->where('column_name', $column)
            ->whereNotNull('referenced_table_name')
            ->exists();
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
        if (! Schema::hasTable('class_attendance')) {
            return;
        }

        if ($this->indexExists('class_attendance', 'class_attendance_subject_student_date_period_unique')) {
            Schema::table('class_attendance', function (Blueprint $table) {
                $table->dropUnique('class_attendance_subject_student_date_period_unique');
            });
        }

        if ($this->foreignKeyExistsForColumn('class_attendance', 'professor_id')) {
            Schema::table('class_attendance', function (Blueprint $table) {
                $table->dropForeign(['professor_id']);
            });
        }

        if (Schema::hasColumn('class_attendance', 'professor_id')) {
            DB::statement('ALTER TABLE `class_attendance` MODIFY `professor_id` BIGINT UNSIGNED NOT NULL');
        }

        if (! $this->foreignKeyExistsForColumn('class_attendance', 'professor_id')) {
            Schema::table('class_attendance', function (Blueprint $table) {
                $table->foreign('professor_id')->references('id')->on('users')->onDelete('cascade');
            });
        }

        if (Schema::hasColumn('class_attendance', 'period_id')) {
            if ($this->foreignKeyExistsForColumn('class_attendance', 'period_id')) {
                Schema::table('class_attendance', function (Blueprint $table) {
                    $table->dropForeign(['period_id']);
                });
            }

            Schema::table('class_attendance', function (Blueprint $table) {
                $table->dropColumn('period_id');
            });
        }

        if (! $this->indexExists('class_attendance', 'unique_attendance_per_class')) {
            Schema::table('class_attendance', function (Blueprint $table) {
                $table->unique(['professor_id', 'subject_id', 'student_id', 'class_date'], 'unique_attendance_per_class');
            });
        }
    }
};
