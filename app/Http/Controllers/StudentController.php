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
     * Estudiantes pendientes y ya asignados en el periodo activo.
     */
    public function index(Request $request)
    {
        $currentPeriod = Period::currentAcademic();
        $previousPeriod = Period::previousChronologicalTo($currentPeriod);

        $filterSubjectId = $request->query('subject_id');

        $filterSubjects = $this->filterSubjectsForPreviousPeriod($previousPeriod);
        $assignableSubjects = Subject::query()->orderBy('Nivel')->orderBy('name')->get();

        if (! $currentPeriod) {
            return view('students.index', [
                'rows' => collect(),
                'assignedRows' => collect(),
                'currentPeriod' => null,
                'previousPeriod' => null,
                'filterSubjects' => $filterSubjects,
                'filterSubjectId' => $filterSubjectId,
                'assignableSubjects' => $assignableSubjects,
                'passByStudent' => [],
            ]);
        }

        $baseQuery = function () {
            return User::query()
                ->where('is_profesor', false)
                ->where('is_admin', false);
        };

        $pendingQuery = $baseQuery();
        $assignedQuery = $baseQuery();

        if (Schema::hasColumn('subject_student', 'period_id')) {
            $pendingQuery->whereDoesntHave('subjectsAsStudent', function ($q) use ($currentPeriod) {
                $q->where('subject_student.period_id', $currentPeriod->id);
            });
            $assignedQuery->whereHas('subjectsAsStudent', function ($q) use ($currentPeriod) {
                $q->where('subject_student.period_id', $currentPeriod->id);
            });
        } else {
            $assignedQuery->whereHas('subjectsAsStudent');
            $pendingQuery->whereDoesntHave('subjectsAsStudent');
        }

        if ($filterSubjectId && $previousPeriod) {
            $applyPrevFilter = function ($q) use ($previousPeriod, $filterSubjectId) {
                $q->whereHas('subjectsAsStudent', function ($sub) use ($previousPeriod, $filterSubjectId) {
                    $sub->where('subject_student.period_id', $previousPeriod->id)
                        ->where('subject_student.subject_id', $filterSubjectId);
                });
            };
            $applyPrevFilter($pendingQuery);
            $applyPrevFilter($assignedQuery);
        }

        $pendingStudents = $pendingQuery->orderBy('name')->get();
        $assignedStudents = $assignedQuery->orderBy('name')->get();

        $assignedSubjectByStudent = [];
        if ($assignedStudents->isNotEmpty() && Schema::hasColumn('subject_student', 'period_id')) {
            $assignedSubjectByStudent = DB::table('subject_student')
                ->where('period_id', $currentPeriod->id)
                ->whereIn('student_id', $assignedStudents->pluck('id'))
                ->get(['student_id', 'subject_id'])
                ->mapWithKeys(fn ($row) => [(int) $row->student_id => (int) $row->subject_id])
                ->all();
        }

        $rows = $this->buildStudentRows($pendingStudents, $previousPeriod);
        $assignedRows = $this->buildStudentRows($assignedStudents, $previousPeriod, $assignedSubjectByStudent);

        $allForPass = $pendingStudents->merge($assignedStudents)->unique('id');
        $passByStudent = $this->latestPassByStudent($allForPass, $assignableSubjects);

        return view('students.index', [
            'rows' => $rows,
            'assignedRows' => $assignedRows,
            'currentPeriod' => $currentPeriod,
            'previousPeriod' => $previousPeriod,
            'filterSubjects' => $filterSubjects,
            'filterSubjectId' => $filterSubjectId,
            'assignableSubjects' => $assignableSubjects,
            'passByStudent' => $passByStudent,
        ]);
    }

    /**
     * Inscribe o desasigna al alumno en el periodo académico actual.
     * subject_id vacío = quitar matrícula del periodo (No asignado).
     */
    public function assignToSubject(Request $request, User $student)
    {
        if ($student->is_profesor || $student->is_admin) {
            return back()->with('error', 'El usuario seleccionado no es un estudiante.');
        }

        $data = $request->validate([
            'subject_id' => 'nullable|integer|exists:subjects,id',
        ]);

        $currentPeriod = Period::currentAcademic();
        if (! $currentPeriod) {
            return back()->with('error', 'No hay un periodo activo para asignar alumnos.');
        }

        $redirect = redirect()->route('students.index', array_filter([
            'subject_id' => $request->query('subject_id') ?: $request->input('filter_subject_id'),
        ]));

        if (Schema::hasColumn('subject_student', 'period_id')) {
            DB::table('subject_student')
                ->where('student_id', $student->id)
                ->where('period_id', $currentPeriod->id)
                ->delete();

            if (! empty($data['subject_id'])) {
                DB::table('subject_student')->updateOrInsert(
                    [
                        'subject_id' => (int) $data['subject_id'],
                        'student_id' => $student->id,
                        'period_id' => $currentPeriod->id,
                    ],
                    [
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]
                );

                $subject = Subject::find($data['subject_id']);

                return $redirect->with(
                    'success',
                    $student->name.' asignado a '.($subject?->name ?? 'la materia').' ('.$currentPeriod->name.').'
                );
            }

            return $redirect->with(
                'success',
                $student->name.' quedó sin asignar en '.$currentPeriod->name.'.'
            );
        }

        if (! empty($data['subject_id'])) {
            $student->subjectsAsStudent()->sync([(int) $data['subject_id']]);
            $subject = Subject::find($data['subject_id']);

            return $redirect->with(
                'success',
                $student->name.' asignado a '.($subject?->name ?? 'la materia').'.'
            );
        }

        $student->subjectsAsStudent()->detach();

        return $redirect->with('success', $student->name.' quedó sin asignar.');
    }

    /**
     * @param  array<int, int>  $assignedSubjectByStudent  student_id => subject_id del periodo actual
     * @return \Illuminate\Support\Collection<int, object{student: User, materia: string, paso: string, assignedSubjectId: int|null}>
     */
    private function buildStudentRows($students, ?Period $previousPeriod, array $assignedSubjectByStudent = []): \Illuminate\Support\Collection
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

        return $students->map(function (User $student) use ($previousPeriod, $enrollmentsByStudent, $gradesByKey, $hasPassedColumn, $assignedSubjectByStudent) {
            $assignedSubjectId = $assignedSubjectByStudent[$student->id] ?? null;

            if (! $previousPeriod) {
                return (object) [
                    'student' => $student,
                    'materia' => '—',
                    'paso' => '—',
                    'assignedSubjectId' => $assignedSubjectId,
                ];
            }

            $enrollments = $enrollmentsByStudent->get($student->id, collect());
            if ($enrollments->isEmpty()) {
                return (object) [
                    'student' => $student,
                    'materia' => 'Nuevo',
                    'paso' => '—',
                    'assignedSubjectId' => $assignedSubjectId,
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
                'assignedSubjectId' => $assignedSubjectId,
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

    /**
     * Último estado passed por alumno × materia (misma lógica que resumen de aprobación).
     *
     * @param  \Illuminate\Support\Collection<int, User>  $students
     * @param  \Illuminate\Support\Collection<int, Subject>  $subjects
     * @return array<int, array<int, bool|null>>
     */
    private function latestPassByStudent($students, $subjects): array
    {
        if ($students->isEmpty() || $subjects->isEmpty()) {
            return [];
        }

        $studentIds = $students->pluck('id');
        $subjectIds = $subjects->pluck('id');
        $hasPassedColumn = Schema::hasColumn('grades', 'passed');

        $map = [];
        foreach ($studentIds as $sid) {
            $map[(int) $sid] = [];
            foreach ($subjectIds as $subId) {
                $map[(int) $sid][(int) $subId] = null;
            }
        }

        if (! $hasPassedColumn) {
            return $map;
        }

        $gradesOrdered = Grade::query()
            ->whereIn('student_id', $studentIds)
            ->whereIn('subject_id', $subjectIds)
            ->orderByDesc('year')
            ->orderByDesc('trimester')
            ->orderByDesc('id')
            ->get(['student_id', 'subject_id', 'passed']);

        foreach ($gradesOrdered as $g) {
            $sid = (int) $g->student_id;
            $subId = (int) $g->subject_id;
            if (! array_key_exists($sid, $map) || ! array_key_exists($subId, $map[$sid])) {
                continue;
            }
            if ($map[$sid][$subId] !== null) {
                continue;
            }
            $map[$sid][$subId] = (bool) $g->passed;
        }

        return $map;
    }
}

