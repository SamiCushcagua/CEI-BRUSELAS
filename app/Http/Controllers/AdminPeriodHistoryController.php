<?php

namespace App\Http\Controllers;

use App\Models\ClassAttendance;
use App\Models\Grade;
use App\Models\Period;
use App\Models\Subject;
use App\Models\User;
use App\Services\CoursePlanService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class AdminPeriodHistoryController extends Controller
{
    public function __construct(
        private readonly CoursePlanService $coursePlanService
    ) {}

    /**
     * Historial de un trimestre: calificaciones + asistencias por materia (admin).
     */
    public function index(Request $request): View
    {
        if (! Auth::check() || ! Auth::user()->is_admin) {
            abort(403);
        }

        $periods = Period::orderBy('year')->orderBy('trimester')->get();

        $period = $request->filled('period_id')
            ? Period::findOrFail($request->integer('period_id'))
            : (Period::currentAcademic() ?? Period::active()->first() ?? $periods->first());

        if (! $period) {
            return view('admin.period-history', [
                'periods' => $periods,
                'period' => null,
                'sundays' => [],
                'courseBlocks' => collect(),
                'noPeriods' => true,
            ]);
        }

        $sundays = $this->coursePlanService->getSundaysForPeriod($period);

        // Materias con maestro Y alumnos en ese periodo
        $subjects = Subject::query()
            ->whereIn('id', function ($q) use ($period) {
                $q->select('subject_id')
                    ->from('subject_professor')
                    ->where('period_id', $period->id);
            })
            ->whereIn('id', function ($q) use ($period) {
                $q->select('subject_id')
                    ->from('subject_student')
                    ->where('period_id', $period->id);
            })
            ->orderByRaw('CASE WHEN Nivel IS NULL OR Nivel = "" THEN 1 ELSE 0 END')
            ->orderBy('Nivel')
            ->orderBy('name')
            ->get();

        $courseBlocks = $subjects->map(function (Subject $subject) use ($period, $sundays) {
            $students = $subject->studentsForPeriod($period)->orderBy('users.name')->get();
            $professors = $subject->professorsForPeriod($period)->orderBy('users.name')->get();

            $gradesByStudent = collect();
            if ($students->isNotEmpty()) {
                $gradesByStudent = Grade::query()
                    ->where('year', (int) $period->year)
                    ->where('trimester', (int) $period->trimester)
                    ->where('subject_id', $subject->id)
                    ->whereIn('student_id', $students->pluck('id'))
                    ->get()
                    ->keyBy(fn (Grade $g) => (int) $g->student_id);
            }

            $attendanceData = [];
            if ($students->isNotEmpty() && $sundays !== []) {
                $records = ClassAttendance::query()
                    ->where('subject_id', $subject->id)
                    ->where('period_id', $period->id)
                    ->whereIn('class_date', $sundays)
                    ->get();

                $map = $records->mapWithKeys(function (ClassAttendance $r) {
                    $dateKey = $r->class_date
                        ? Carbon::parse($r->class_date)->format('Y-m-d')
                        : '';

                    return [$r->student_id.'|'.$dateKey => $r];
                });

                foreach ($students as $student) {
                    foreach ($sundays as $sunday) {
                        $attendanceData[$student->id][$sunday] = $map->get($student->id.'|'.$sunday);
                    }
                }
            }

            return [
                'subject' => $subject,
                'professors' => $professors,
                'students' => $students,
                'gradesByStudent' => $gradesByStudent,
                'attendanceData' => $attendanceData,
            ];
        });

        return view('admin.period-history', [
            'periods' => $periods,
            'period' => $period,
            'sundays' => $sundays,
            'courseBlocks' => $courseBlocks,
            'noPeriods' => false,
        ]);
    }

    /**
     * Guardar calificaciones y asistencias de una materia en el periodo consultado.
     */
    public function updateCourse(Request $request): JsonResponse
    {
        if (! Auth::check() || ! Auth::user()->is_admin) {
            abort(403);
        }

        $validated = $request->validate([
            'period_id' => 'required|exists:periods,id',
            'subject_id' => 'required|exists:subjects,id',
            'grades' => 'nullable|array',
            'grades.*.student_id' => 'required|integer|exists:users,id',
            'grades.*.task_score' => 'nullable|numeric|min:0|max:100',
            'grades.*.exam_score1' => 'nullable|numeric|min:0|max:100',
            'grades.*.exam_score2' => 'nullable|numeric|min:0|max:100',
            'grades.*.participation_score' => 'nullable|numeric|min:0|max:100',
            'grades.*.bible_score' => 'nullable|numeric|min:0|max:100',
            'grades.*.text_score' => 'nullable|numeric|min:0|max:100',
            'grades.*.other_score' => 'nullable|numeric|min:0|max:100',
            'grades.*.passed' => 'nullable|boolean',
            'attendance' => 'nullable|array',
            'attendance.*.student_id' => 'required|integer|exists:users,id',
            'attendance.*.class_date' => 'required|date',
            'attendance.*.attendance_status' => 'nullable|in:present,absent,late',
            'attendance.*.bible_verse_delivered' => 'nullable|boolean',
        ]);

        $period = Period::findOrFail((int) $validated['period_id']);
        $subjectId = (int) $validated['subject_id'];
        $year = (int) $period->year;
        $trimester = (int) $period->trimester;

        try {
            DB::beginTransaction();

            foreach ($validated['grades'] ?? [] as $row) {
                $studentId = (int) $row['student_id'];
                $student = User::query()->find($studentId);
                if (! $student || ! $student->isStudent()) {
                    continue;
                }

                Grade::updateOrCreate(
                    [
                        'student_id' => $studentId,
                        'subject_id' => $subjectId,
                        'trimester' => $trimester,
                        'year' => $year,
                    ],
                    [
                        'task_score' => $this->nullableScore($row['task_score'] ?? null),
                        'exam_score1' => $this->nullableScore($row['exam_score1'] ?? null),
                        'exam_score2' => $this->nullableScore($row['exam_score2'] ?? null),
                        'participation_score' => $this->nullableScore($row['participation_score'] ?? null),
                        'bible_score' => $this->nullableScore($row['bible_score'] ?? null),
                        'text_score' => $this->nullableScore($row['text_score'] ?? null),
                        'other_score' => $this->nullableScore($row['other_score'] ?? null),
                        'passed' => array_key_exists('passed', $row) ? (bool) $row['passed'] : false,
                    ]
                );
            }

            foreach ($validated['attendance'] ?? [] as $row) {
                $studentId = (int) $row['student_id'];
                $classDate = Carbon::parse($row['class_date'])->format('Y-m-d');
                $status = $row['attendance_status'] ?? null;

                if ($status === null || $status === '') {
                    ClassAttendance::query()
                        ->where('subject_id', $subjectId)
                        ->where('student_id', $studentId)
                        ->where('class_date', $classDate)
                        ->where('period_id', $period->id)
                        ->delete();
                    continue;
                }

                ClassAttendance::updateOrCreate(
                    [
                        'subject_id' => $subjectId,
                        'student_id' => $studentId,
                        'class_date' => $classDate,
                        'period_id' => $period->id,
                    ],
                    [
                        'professor_id' => Auth::id(),
                        'attendance_status' => $status,
                        'bible_verse_delivered' => (bool) ($row['bible_verse_delivered'] ?? false),
                    ]
                );
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Cambios guardados correctamente',
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Error al guardar: '.$e->getMessage(),
            ], 500);
        }
    }

    private function nullableScore(mixed $value): ?float
    {
        if ($value === null || $value === '') {
            return null;
        }

        return (float) $value;
    }
}
