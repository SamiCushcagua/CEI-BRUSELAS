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
        if (! Schema::hasTable('subject_professor')) {
            return;
        }

        if (Schema::hasColumn('subject_professor', 'period_id')) {
            $periodId = $this->ensureAtLeastOnePeriodExists();
            if ($periodId) {
                DB::table('subject_professor')->whereNull('period_id')->update(['period_id' => $periodId]);
            }

            return;
        }

        $periodId = $this->ensureAtLeastOnePeriodExists();

        Schema::table('subject_professor', function (Blueprint $table) {
            $table->foreignId('period_id')
                ->nullable()
                ->after('professor_id')
                ->constrained('periods')
                ->cascadeOnDelete();
        });

        if ($periodId) {
            DB::table('subject_professor')->update(['period_id' => $periodId]);
        }

        DB::statement('ALTER TABLE `subject_professor` MODIFY `period_id` BIGINT UNSIGNED NOT NULL');

        if (! $this->indexExists('subject_professor', 'subject_professor_subject_id_index')) {
            Schema::table('subject_professor', function (Blueprint $table) {
                $table->index('subject_id', 'subject_professor_subject_id_index');
            });
        }
        if (! $this->indexExists('subject_professor', 'subject_professor_professor_id_index')) {
            Schema::table('subject_professor', function (Blueprint $table) {
                $table->index('professor_id', 'subject_professor_professor_id_index');
            });
        }

        if ($this->indexExists('subject_professor', 'subject_professor_subject_id_professor_id_unique')) {
            Schema::table('subject_professor', function (Blueprint $table) {
                $table->dropUnique('subject_professor_subject_id_professor_id_unique');
            });
        }

        if (! $this->indexExists('subject_professor', 'subject_professor_subject_id_professor_id_period_id_unique')) {
            Schema::table('subject_professor', function (Blueprint $table) {
                $table->unique(
                    ['subject_id', 'professor_id', 'period_id'],
                    'subject_professor_subject_id_professor_id_period_id_unique'
                );
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (! Schema::hasTable('subject_professor') || ! Schema::hasColumn('subject_professor', 'period_id')) {
            return;
        }

        if ($this->indexExists('subject_professor', 'subject_professor_subject_id_professor_id_period_id_unique')) {
            Schema::table('subject_professor', function (Blueprint $table) {
                $table->dropUnique('subject_professor_subject_id_professor_id_period_id_unique');
            });
        }

        if (! $this->indexExists('subject_professor', 'subject_professor_subject_id_professor_id_unique')) {
            Schema::table('subject_professor', function (Blueprint $table) {
                $table->unique(['subject_id', 'professor_id'], 'subject_professor_subject_id_professor_id_unique');
            });
        }

        Schema::table('subject_professor', function (Blueprint $table) {
            $table->dropForeign(['period_id']);
        });
        Schema::table('subject_professor', function (Blueprint $table) {
            $table->dropColumn('period_id');
        });
    }

    private function ensureAtLeastOnePeriodExists(): int
    {
        if (! Schema::hasTable('periods')) {
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

        $existingPeriodId = DB::table('periods')->orderBy('id')->value('id');
        if ($existingPeriodId) {
            return (int) $existingPeriodId;
        }

        $now = now();
        $month = (int) $now->format('n');
        $trimester = $month <= 4 ? 1 : ($month <= 8 ? 2 : 3);
        $year = (int) $now->format('Y');

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
};
