<?php

namespace App\Http\Controllers;

use App\Models\Grade;
use App\Models\Period;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class StudentController extends Controller
{
    /**
     * Estudiantes sin matrícula en el periodo activo, con contexto del periodo anterior.
     */
    public function index(Request $request)
    {
        $currentPeriod = Period::currentAcademic();
        $previousPeriod = Period::previousChronologicalTo($currentPeriod);

        $filterSubjectId = $request->query('subject_id');

        $filterSubjects = $this->filterSubjectsForPreviousPeriod($previousPeriod);

        if (! $currentPeriod) {
            return view('students.index', [
                'rows' => collect(),
                'currentPeriod' => null,
                'previousPeriod' => null,
                'filterSubjects' => $filterSubjects,
                'filterSubjectId' => $filterSubjectId,
            ]);
        }

        $query = User::query()
            ->where('is_profesor', false)
            ->where('is_admin', false);

        if (Schema::hasColumn('subject_student', 'period_id')) {
            $query->whereDoesntHave('subjectsAsStudent', function ($q) use ($currentPeriod) {
                $q->where('subject_student.period_id', $currentPeriod->id);
            });
        }

        if ($filterSubjectId && $previousPeriod) {
            $query->whereHas('subjectsAsStudent', function ($q) use ($previousPeriod, $filterSubjectId) {
                $q->where('subject_student.period_id', $previousPeriod->id)
                    ->where('subject_student.subject_id', $filterSubjectId);
            });
        }

        $students = $query->orderBy('name')->get();

        $rows = $this->buildStudentRows($students, $previousPeriod);

        return view('students.index', [
            'rows' => $rows,
            'currentPeriod' => $currentPeriod,
            'previousPeriod' => $previousPeriod,
            'filterSubjects' => $filterSubjects,
            'filterSubjectId' => $filterSubjectId,
        ]);
    }

    /**
     * @return \Illuminate\Support\Collection<int, object{student: User, materia: string, paso: string}>
     */
    private function buildStudentRows($students, ?Period $previousPeriod): \Illuminate\Support\Collection
    {
        if ($students->isEmpty()) {
            return collect();
        }

        $studentIds = $students->pluck('id');
        $enrollmentsByStudent = collect();
        $gradesByKey = collect();

        if ($previousPeriod) {
            $enrollmentsByStudent = DB::table('subject_student')
                ->join('subjects', 'subjects.id', '=', 'subject_student.subject_id')
                ->where('subject_student.period_id', $previousPeriod->id)
                ->whereIn('subject_student.student_id', $studentIds)
                ->select(
                    'subject_student.student_id',
                    'subjects.id as subject_id',
                    'subjects.name as subject_name'
                )
                ->orderBy('subjects.name')
                ->get()
                ->groupBy('student_id');

            $gradesQuery = Grade::query()
                ->whereIn('student_id', $studentIds)
                ->where('year', $previousPeriod->year)
                ->where('trimester', $previousPeriod->trimester);

            $gradesByKey = $gradesQuery->get()->keyBy(fn (Grade $g) => $g->student_id.'_'.$g->subject_id);
        }

        $hasPassedColumn = Schema::hasColumn('grades', 'passed');

        return $students->map(function (User $student) use ($previousPeriod, $enrollmentsByStudent, $gradesByKey, $hasPassedColumn) {
            if (! $previousPeriod) {
                return (object) [
                    'student' => $student,
                    'materia' => '—',
                    'paso' => '—',
                ];
            }

            $enrollments = $enrollmentsByStudent->get($student->id, collect());
            if ($enrollments->isEmpty()) {
                return (object) [
                    'student' => $student,
                    'materia' => 'Nuevo',
                    'paso' => '—',
                ];
            }

            $names = $enrollments->pluck('subject_name')->unique()->values()->implode(', ');

            $passedStates = [];
            foreach ($enrollments as $e) {
                $key = $student->id.'_'.$e->subject_id;
                $grade = $gradesByKey->get($key);
                if (! $grade || ! $hasPassedColumn) {
                    $passedStates[] = null;
                } else {
                    $passedStates[] = (bool) $grade->passed;
                }
            }

            $paso = '—';
            $collection = collect($passedStates);
            if ($collection->isNotEmpty()) {
                if ($collection->every(fn ($p) => $p === true)) {
                    $paso = 'Sí';
                } elseif ($collection->contains(fn ($p) => $p === false)) {
                    $paso = 'No';
                }
            }

            return (object) [
                'student' => $student,
                'materia' => $names,
                'paso' => $paso,
            ];
        });
    }

    private function filterSubjectsForPreviousPeriod(?Period $previousPeriod)
    {
        if (! $previousPeriod) {
            return Subject::query()->orderBy('name')->get();
        }

        $ids = DB::table('subject_student')
            ->where('period_id', $previousPeriod->id)
            ->distinct()
            ->pluck('subject_id');

        if ($ids->isEmpty()) {
            return Subject::query()->orderBy('name')->get();
        }

        return Subject::query()
            ->whereIn('id', $ids)
            ->orderBy('name')
            ->get();
    }
}

