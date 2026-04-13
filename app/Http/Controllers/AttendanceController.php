<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Auth;
use App\Models\ClassAttendance;
use App\Models\Subject;
use Illuminate\Support\Facades\Log;
use App\Models\Period;
use App\Models\User;
use Carbon\Carbon;

class AttendanceController extends Controller
{
    /**
     * Mostrar la vista principal de asistencia
     */



    public function index(Request $request)
    {
        $user = User::query()->findOrFail(Auth::id());
        $period = Period::active()->firstOrFail();
        $sundays = $this->getSundaysForPeriod($period);

        if ($user->isStudent()) {
            return $this->studentAttendanceIndex($request, $user, $period, $sundays);
        }

        $professor = $user;
        $currentTrimester = $period->trimester;

        // Solo materias asignadas al profesor en el periodo activo (evita duplicados por otros trimestres)
        $professorSubjects = $professor->subjects();
        if (Schema::hasColumn('subject_professor', 'period_id')) {
            $professorSubjects->wherePivot('period_id', $period->id);
        }
        $subjects = $professorSubjects->orderBy('subjects.name')->get();

        $defaultSunday = $this->getClosestSunday($sundays);

        $selectedSubject = null;
        if ($subjects->count() === 1) {
            $selectedSubject = $subjects->first();
        } else {
            $subjectId = $request->get('subject_id');
            if ($subjectId && $subjects->contains('id', (int) $subjectId)) {
                $selectedSubject = Subject::find($subjectId);
            }
        }

        $selectedDateRaw = $request->get('class_date', $defaultSunday);
        $selectedDate = $this->parseDateParam($selectedDateRaw);
        $students = collect();
        $attendanceRecords = collect();
        $attendanceData = [];

        if ($selectedSubject && $selectedDate) {
            $students = $selectedSubject->studentsForPeriod($period)->get();

            // Asistencia compartida: misma fila para todos los profesores de la materia en este periodo
            $attendanceRecords = ClassAttendance::where('subject_id', $selectedSubject->id)
                ->where('period_id', $period->id)
                ->whereDate('class_date', $selectedDate)
                ->get()
                ->keyBy('student_id');

            $allAttendanceRecords = ClassAttendance::where('subject_id', $selectedSubject->id)
                ->where('period_id', $period->id)
                ->whereIn('class_date', $sundays)
                ->get();

            // Organizar datos por estudiante y fecha
            foreach ($students as $student) {
                $attendanceData[$student->id] = [];
                foreach ($sundays as $sunday) {
                    $record = $allAttendanceRecords->where('student_id', $student->id)
                        ->where('class_date', '>=', $sunday)
                        ->where('class_date', '<', date('Y-m-d', strtotime($sunday . ' +1 day')))
                        ->first();

                    $attendanceData[$student->id][$sunday] = $record;
                }
            }
        }

        return view('attendance.index', compact(
            'subjects',
            'sundays',
            'defaultSunday',
            'currentTrimester',
            'selectedSubject',
            'selectedDate',
            'students',
            'attendanceRecords',
            'attendanceData',
            'period'
        ))->with('isStudentView', false);
    }

    /**
     * Evita 500 si class_date viene vacío o inválido en la query (?class_date=).
     */
    private function parseDateParam(mixed $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }
        if (is_string($value) && trim($value) === '') {
            return null;
        }
        try {
            return Carbon::parse($value)->format('Y-m-d');
        } catch (\Throwable) {
            return null;
        }
    }

    /**
     * Vista solo lectura: el estudiante ve sus asistencias del periodo activo por materia.
     */
    private function studentAttendanceIndex(Request $request, User $student, Period $period, array $sundays)
    {
        $subjectsQuery = $student->subjectsAsStudent();
        if (Schema::hasColumn('subject_student', 'period_id')) {
            $subjectsQuery->wherePivot('period_id', $period->id);
        }
        $subjects = $subjectsQuery->orderBy('subjects.name')->get();

        $selectedSubject = null;
        $studentAttendanceByDate = [];

        if ($request->filled('subject_id')) {
            $selectedSubject = Subject::find($request->get('subject_id'));
            if ($selectedSubject && $subjects->contains('id', $selectedSubject->id)) {
                $records = ClassAttendance::query()
                    ->where('subject_id', $selectedSubject->id)
                    ->where('period_id', $period->id)
                    ->where('student_id', $student->id)
                    ->whereIn('class_date', $sundays)
                    ->get();

                foreach ($sundays as $d) {
                    $studentAttendanceByDate[$d] = $records->first(function ($r) use ($d) {
                        if (! $r->class_date) {
                            return false;
                        }

                        try {
                            return Carbon::parse($r->class_date)->toDateString() === $d;
                        } catch (\Throwable) {
                            return false;
                        }
                    });
                }
            }
        }

        return view('attendance.index', [
            'isStudentView' => true,
            'studentUser' => $student,
            'subjects' => $subjects,
            'sundays' => $sundays,
            'period' => $period,
            'currentTrimester' => $period->trimester,
            'selectedSubject' => $selectedSubject,
            'studentAttendanceByDate' => $studentAttendanceByDate,
            'defaultSunday' => null,
            'selectedDate' => null,
            'students' => collect(),
            'attendanceRecords' => collect(),
            'attendanceData' => [],
        ]);
    }
    /**
     * Guardar los registros de asistencia
     */
    public function store(Request $request)
    {
        $professor = User::query()->findOrFail(Auth::id());

        if ($professor->isStudent()) {
            abort(403, 'Solo el personal autorizado puede registrar asistencia.');
        }

        $validated = $request->validate([
            'subject_id' => 'required|integer|exists:subjects,id',
            'class_date' => 'required|date',
            'attendance' => 'required|array',
            'attendance.*.student_id' => 'required|integer|exists:users,id',
            'attendance.*.attendance_status' => 'required|in:present,absent,late',
            'attendance.*.bible_verse_delivered' => 'boolean',
            'attendance.*.notes' => 'nullable|string|max:500'
        ]);

        // Verificar que el profesor tenga acceso a esta materia en el periodo activo
        $period = Period::active()->firstOrFail();
        $subject = Subject::find($validated['subject_id']);
        if (! $subject) {
            return redirect()->back()->with('error', 'Materia no encontrada.');
        }
        $accessQuery = $professor->subjects()->where('subjects.id', $subject->id);
        if (Schema::hasColumn('subject_professor', 'period_id')) {
            $accessQuery->wherePivot('period_id', $period->id);
        }
        $hasAccess = $accessQuery->exists();
        if (! $hasAccess) {
            return redirect()->back()->with('error', 'No tienes acceso a esta materia.');
        }

        $classDate = \Carbon\Carbon::parse($validated['class_date'])->format('Y-m-d');

        // Guardar o actualizar: una fila por materia + alumno + fecha + periodo (professor_id = último que guardó)
        foreach ($validated['attendance'] as $attendanceData) {
            ClassAttendance::updateOrCreate(
                [
                    'subject_id' => $validated['subject_id'],
                    'student_id' => $attendanceData['student_id'],
                    'class_date' => $classDate,
                    'period_id' => $period->id,
                ],
                [
                    'professor_id' => $professor->id,
                    'attendance_status' => $attendanceData['attendance_status'],
                    'bible_verse_delivered' => $attendanceData['bible_verse_delivered'] ?? false,
                    'notes' => $attendanceData['notes'] ?? null,
                ]
            );
        }

        return redirect()->route('attendance.index', [
            'subject_id' => $validated['subject_id'],
            'class_date' => $classDate,
        ])->with('success', 'Asistencia guardada exitosamente.');
    }



    /**
     * Obtener el trimestre actual basado en la fecha de hoy
     */
    private function getCurrentTrimester()
    {
        $today = new \DateTime();
        $month = (int) $today->format('n'); // 1-12

        if ($month >= 1 && $month <= 4) {
            return 1; // Enero - Abril
        } elseif ($month >= 5 && $month <= 8) {
            return 2; // Mayo - Agosto
        } else {
            return 3; // Septiembre - Diciembre
        }
    }

    /**
     * Domingos de clase dentro del periodo. Usa start_date / end_date cuando existen.
     * El cálculo solo por trimestre+meses ignoraba esas fechas (p. ej. T2 → solo mayo–ago).
     */
    private function getSundaysForPeriod(Period $period): array
    {
        if ($period->start_date && $period->end_date) {
            return $this->getSundaysBetween(
                Carbon::parse($period->start_date)->startOfDay(),
                Carbon::parse($period->end_date)->startOfDay()
            );
        }

        return $this->getSundaysFromLegacyTrimesterMonths($period);
    }

    /**
     * @return list<string>
     */
    private function getSundaysBetween(Carbon $start, Carbon $end): array
    {
        if ($start->gt($end)) {
            return [];
        }

        $cursor = $start->copy();
        while (! $cursor->isSunday()) {
            $cursor->addDay();
            if ($cursor->gt($end)) {
                return [];
            }
        }

        $sundays = [];
        while ($cursor->lte($end)) {
            $sundays[] = $cursor->format('Y-m-d');
            $cursor->addWeek();
        }

        return $sundays;
    }

    /**
     * Compatibilidad: periodos sin start_date / end_date en BD.
     */
    private function getSundaysFromLegacyTrimesterMonths(Period $period): array
    {
        $year = $period->year;
        $currentTrimester = $period->trimester;

        $sundays = [];
        $monthRanges = [
            1 => [1, 2, 3, 4],
            2 => [5, 6, 7, 8],
            3 => [9, 10, 11, 12],
        ];
        $months = $monthRanges[$currentTrimester] ?? [1, 2, 3, 4];

        foreach ($months as $month) {
            $startDate = new \DateTime("$year-$month-01");
            $endDate = new \DateTime("$year-$month-".$startDate->format('t'));
            while ($startDate->format('N') != 7) {
                $startDate->add(new \DateInterval('P1D'));
            }
            while ($startDate <= $endDate) {
                $sundays[] = $startDate->format('Y-m-d');
                $startDate->add(new \DateInterval('P7D'));
            }
        }

        return $sundays;
    }

    /**
     * Obtener el domingo más cercano dentro de una lista ya calculada.
     * Esto se usa en index() para definir la fecha por defecto al cargar la pantalla.
     */
    private function getClosestSunday(array $sundays): ?string
    {
        if (empty($sundays)) {
            return null;
        }

        $today = new \DateTime();
        $closestSunday = null;
        $minDifference = PHP_INT_MAX;

        foreach ($sundays as $sunday) {
            $sundayDate = new \DateTime($sunday);
            $difference = abs($today->diff($sundayDate)->days);

            if ($difference < $minDifference) {
                $minDifference = $difference;
                $closestSunday = $sunday;
            }
        }

        return $closestSunday;
    }

    /**
     * Obtener el domingo más cercano del trimestre actual
     */
    private function getClosestSundayInCurrentTrimester()
    {
        $period = Period::active()->firstOrFail();
        $sundays = $this->getSundaysForPeriod($period);

        return $this->getClosestSunday($sundays);
    }
}
