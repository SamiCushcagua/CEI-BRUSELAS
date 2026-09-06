<?php

namespace App\Http\Controllers;

use App\Models\Subject;
use App\Models\User;
use App\Models\Period;
use App\Models\Grade;
use App\Models\ClassAttendance;
use App\Services\CoursePlanService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class SubjectRelationshipController extends Controller
{
    // Asignar profesor a una materia (por periodo activo)
    public function assignProfessor(Request $request, Subject $subject)
    {
        $request->validate([
            'professor1' => 'nullable|exists:users,id',
            'professor2' => 'nullable|exists:users,id',
        ]);

        $period = Period::active()->firstOrFail();

        // Reemplazo por periodo: eliminar cualquier asignación previa para este subject+period
        // para que el cambio de maestro se refleje correctamente y no se acumulen filas.
        // Si ambos selects van vacíos, la materia queda sin profesores en el periodo activo.
        DB::table('subject_professor')
            ->where('subject_id', $subject->id)
            ->where('period_id', $period->id)
            ->delete();

        $professor1Id = $request->filled('professor1') ? (int) $request->professor1 : null;
        $professor2Id = $request->filled('professor2') ? (int) $request->professor2 : null;

        if ($professor1Id) {
            $professor1 = User::findOrFail($professor1Id);

            if (!$professor1->is_profesor) {
                return back()->with('error', 'El primer usuario seleccionado no es un profesor.');
            }

            DB::table('subject_professor')->updateOrInsert(
                [
                    'subject_id'   => $subject->id,
                    'professor_id' => $professor1->id,
                    'period_id'    => $period->id,
                ],
                [
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }

        if ($professor2Id) {
            $professor2 = User::findOrFail($professor2Id);

            if (!$professor2->is_profesor) {
                return back()->with('error', 'El segundo usuario seleccionado no es un profesor.');
            }

            if ($professor1Id && $professor2->id === $professor1Id) {
                return back()->with('error', 'El segundo profesor no puede ser el mismo que el primero.');
            }

            DB::table('subject_professor')->updateOrInsert(
                [
                    'subject_id'   => $subject->id,
                    'professor_id' => $professor2->id,
                    'period_id'    => $period->id,
                ],
                [
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }

        if ($professor1Id || $professor2Id) {
            app(CoursePlanService::class)->findOrCreateForSubjectPeriod($subject, $period);
            return back()->with('success', 'Profesores asignados exitosamente.');
        }

        return back()->with('success', 'Profesores desasignados exitosamente.');
    }
    // Remover profesor de una materia (solo del periodo activo)
    public function removeProfessor(Subject $subject, User $professor)
    {
        $period = Period::active()->firstOrFail();

        DB::table('subject_professor')
            ->where('subject_id', $subject->id)
            ->where('professor_id', $professor->id)
            ->where('period_id', $period->id)
            ->delete();

        return back()->with('success', 'Profesor removido exitosamente.');
    }

    // Inscribir estudiante a una materia
    public function enrollStudent(Request $request, Subject $subject)
    {
        $request->validate([
            'student_id' => 'required|exists:users,id'
        ]);

        $student = User::findOrFail($request->student_id);
        
        if ($student->is_profesor || $student->is_admin) {
            return back()->with('error', 'El usuario seleccionado no es un estudiante.');
        }

        $period = Period::active()->firstOrFail();

        // Insertar o mantener la inscripción solo para el periodo actual
        DB::table('subject_student')->updateOrInsert(
            [
                'subject_id' => $subject->id,
                'student_id' => $student->id,
                'period_id'  => $period->id,
            ],
            [
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        return back()->with('success', 'Estudiante inscrito exitosamente.');
    }

    // Remover estudiante de una materia
    public function removeStudent(Subject $subject, User $student)
    {
        $period = Period::active()->firstOrFail();

        DB::table('subject_student')
            ->where('subject_id', $subject->id)
            ->where('student_id', $student->id)
            ->where('period_id', $period->id)
            ->delete();

        return back()->with('success', 'Estudiante removido exitosamente.');
    }

    // Asignar estudiante a un profesor
    public function assignStudentToProfessor(Request $request, User $professor)
    {
        $request->validate([
            'student_id' => 'required|exists:users,id'
        ]);

        $student = User::findOrFail($request->student_id);
        
        if ($student->role !== 'student') {
            return back()->with('error', 'El usuario seleccionado no es un estudiante.');
        }

        $professor->students()->attach($student->id);

        return back()->with('success', 'Estudiante asignado al profesor exitosamente.');
    }

    // Remover estudiante de un profesor
    public function removeStudentFromProfessor(User $professor, User $student)
    {
        $professor->students()->detach($student->id);
        return back()->with('success', 'Estudiante removido del profesor exitosamente.');
    }

    // Obtener materias de un profesor
    public function getProfessorSubjects(User $professor)
    {
        $subjects = $professor->subjectsAsProfessor;
        return view('subjects.professor-subjects', compact('subjects', 'professor'));
    }

    // Obtener materias de un estudiante (curso actual + historial)
    public function getStudentSubjects(User $student)
    {
        $this->authorizeStudentSubjectsAccess($student);

        $currentPeriod = Period::currentAcademic() ?? Period::active()->first();

        $currentSubjects = collect();
        $historyRows = collect();

        $pivotCols = array_values(array_filter(
            ['period_id', 'diploma_delivered'],
            fn ($c) => Schema::hasColumn('subject_student', $c)
        ));

        if ($currentPeriod && Schema::hasColumn('subject_student', 'period_id')) {
            $currentQuery = $student->subjectsAsStudent();
            if ($pivotCols !== []) {
                $currentQuery->withPivot(...$pivotCols);
            }
            $currentSubjects = $currentQuery
                ->wherePivot('period_id', $currentPeriod->id)
                ->orderBy('subjects.Nivel')
                ->orderBy('subjects.name')
                ->get();

            $historyEnrollments = DB::table('subject_student as ss')
                ->join('subjects', 'subjects.id', '=', 'ss.subject_id')
                ->join('periods', 'periods.id', '=', 'ss.period_id')
                ->where('ss.student_id', $student->id)
                ->where('ss.period_id', '!=', $currentPeriod->id)
                ->select(
                    'ss.subject_id',
                    'ss.period_id',
                    'subjects.name as subject_name',
                    'subjects.Nivel as subject_nivel',
                    'periods.name as period_name',
                    'periods.year as period_year',
                    'periods.trimester as period_trimester'
                )
                ->orderByDesc('periods.year')
                ->orderByDesc('periods.trimester')
                ->orderBy('subjects.name')
                ->get();

            $historyRows = $this->attachPassStatusToHistory($historyEnrollments, $student->id);
        } else {
            // Fallback sin period_id: todo como "actual", sin historial separado
            $currentQuery = $student->subjectsAsStudent();
            if ($pivotCols !== []) {
                $currentQuery->withPivot(...$pivotCols);
            }
            $currentSubjects = $currentQuery->orderBy('subjects.name')->get();
        }

        return view('subjects.student-subjects', compact(
            'student',
            'currentPeriod',
            'currentSubjects',
            'historyRows'
        ));
    }

    /**
     * Detalle de un curso pasado: notas, asistencias y maestros de ese periodo.
     */
    public function showStudentSubjectHistory(User $student, Subject $subject, Request $request)
    {
        $this->authorizeStudentSubjectsAccess($student);

        $period = Period::findOrFail($request->integer('period_id'));

        $enrolled = DB::table('subject_student')
            ->where('student_id', $student->id)
            ->where('subject_id', $subject->id)
            ->where('period_id', $period->id)
            ->exists();

        if (! $enrolled) {
            abort(404, 'No hay inscripción de este alumno en esa materia para el periodo indicado.');
        }

        $grade = Grade::query()
            ->where('student_id', $student->id)
            ->where('subject_id', $subject->id)
            ->where('year', (int) $period->year)
            ->where('trimester', (int) $period->trimester)
            ->first();

        $professors = $subject->professorsForPeriod($period)->orderBy('users.name')->get();

        $sundays = app(CoursePlanService::class)->getSundaysForPeriod($period);
        $attendanceByDate = [];
        if ($sundays !== []) {
            $records = ClassAttendance::query()
                ->where('student_id', $student->id)
                ->where('subject_id', $subject->id)
                ->where('period_id', $period->id)
                ->whereIn('class_date', $sundays)
                ->get()
                ->keyBy(fn (ClassAttendance $r) => $r->class_date?->format('Y-m-d'));

            foreach ($sundays as $date) {
                $attendanceByDate[$date] = $records->get($date);
            }
        }

        return view('subjects.student-subject-history', compact(
            'student',
            'subject',
            'period',
            'grade',
            'professors',
            'sundays',
            'attendanceByDate'
        ));
    }

    private function authorizeStudentSubjectsAccess(User $student): void
    {
        $user = Auth::user();
        if (! $user) {
            abort(403);
        }

        if ($user->is_admin || (int) $user->id === (int) $student->id) {
            return;
        }

        abort(403, 'No tienes permiso para ver las materias de este estudiante.');
    }

    /**
     * @param  \Illuminate\Support\Collection<int, object>  $rows
     * @return \Illuminate\Support\Collection<int, object>
     */
    private function attachPassStatusToHistory($rows, int $studentId)
    {
        if ($rows->isEmpty()) {
            return collect();
        }

        $hasPassed = Schema::hasColumn('grades', 'passed');
        $grades = collect();

        if ($hasPassed) {
            $years = $rows->pluck('period_year')->unique()->filter()->all();
            $grades = Grade::query()
                ->where('student_id', $studentId)
                ->whereIn('year', $years)
                ->whereIn('subject_id', $rows->pluck('subject_id')->unique())
                ->get()
                ->keyBy(fn (Grade $g) => $g->subject_id.'_'.$g->year.'_'.$g->trimester);
        }

        return $rows->map(function ($row) use ($grades, $hasPassed) {
            $paso = '—';
            if ($hasPassed) {
                $key = $row->subject_id.'_'.$row->period_year.'_'.$row->period_trimester;
                $grade = $grades->get($key);
                if ($grade) {
                    $paso = $grade->passed ? 'Sí' : 'No';
                }
            }

            return (object) [
                'subject_id' => (int) $row->subject_id,
                'period_id' => (int) $row->period_id,
                'subject_name' => $row->subject_name,
                'subject_nivel' => $row->subject_nivel,
                'period_name' => $row->period_name,
                'period_year' => $row->period_year,
                'period_trimester' => $row->period_trimester,
                'paso' => $paso,
            ];
        });
    }

    // Obtener estudiantes de un profesor
    public function getProfessorStudents(User $professor)
    {
        $period = Period::active()->firstOrFail();

        $studentIds = DB::table('subject_student as ss')
            ->join('subject_professor as sp', function ($join) {
                $join->on('sp.subject_id', '=', 'ss.subject_id')
                    ->on('sp.period_id', '=', 'ss.period_id');
            })
            ->where('sp.professor_id', $professor->id)
            ->where('ss.period_id', $period->id)
            ->distinct()
            ->pluck('ss.student_id');

        $students = User::whereIn('id', $studentIds)
            ->where('is_profesor', false)
            ->where('is_admin', false)
            ->orderBy('name')
            ->get();

        $subjectCounts = collect();
        if ($studentIds->isNotEmpty()) {
            $subjectCounts = DB::table('subject_student as ss')
                ->join('subject_professor as sp', function ($join) {
                    $join->on('sp.subject_id', '=', 'ss.subject_id')
                        ->on('sp.period_id', '=', 'ss.period_id');
                })
                ->where('sp.professor_id', $professor->id)
                ->where('ss.period_id', $period->id)
                ->whereIn('ss.student_id', $studentIds)
                ->groupBy('ss.student_id')
                ->select('ss.student_id', DB::raw('COUNT(DISTINCT ss.subject_id) as cnt'))
                ->pluck('cnt', 'student_id');
        }

        $availableStudents = User::where('is_profesor', false)
            ->where('is_admin', false)
            ->whereNotIn('id', $studentIds)
            ->orderBy('name')
            ->get();

        return view('users.professor-students', compact(
            'students',
            'professor',
            'availableStudents',
            'period',
            'subjectCounts'
        ));
    }

    // Obtener profesores de un estudiante
    public function getStudentProfessors(User $student)
    {
        // Importante: los profesores deben listarse SOLO para el periodo activo.
        // Evitamos usar relaciones "globales" que mezclan asignaciones de trimestres distintos.
        $period = Period::active()->firstOrFail();

        $professorIds = DB::table('subject_student as ss')
            ->join('subject_professor as sp', 'sp.subject_id', '=', 'ss.subject_id')
            ->where('ss.student_id', $student->id)
            ->where('ss.period_id', $period->id)
            ->where('sp.period_id', $period->id)
            ->select('sp.professor_id')
            ->distinct()
            ->pluck('sp.professor_id');

        $professors = $professorIds->isNotEmpty()
            ? User::whereIn('id', $professorIds)->orderBy('name')->get()
            : collect();

        return view('users.student-professors', compact('professors', 'student'));
    }
} 