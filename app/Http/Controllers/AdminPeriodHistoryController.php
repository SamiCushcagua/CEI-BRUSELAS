<?php

namespace App\Http\Controllers;

use App\Models\ClassAttendance;
use App\Models\Grade;
use App\Models\Period;
use App\Models\Subject;
use App\Services\CoursePlanService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AdminPeriodHistoryController extends Controller
{
    public function __construct(
        private readonly CoursePlanService $coursePlanService
    ) {}

    /**
     * Historial de un trimestre: calificaciones + asistencias por materia (solo admin, solo lectura).
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
}
