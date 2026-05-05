<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Period;
use App\Models\Subject;
use App\Models\ClassAttendance;
use Carbon\Carbon;

class AdminPeriodSubjectDashboardController extends Controller
{
    public function index(Request $request)
    {
        if (!Auth::check() || !Auth::user()->is_admin) {
            abort(403);
        }

        $period = isset($request->period_id)
            ? Period::findOrFail($request->period_id)
            : Period::active()->firstOrFail();

        $periods = Period::orderBy('year')->orderBy('trimester')->get();

        // Materias con estudiantes inscritos para este periodo (para que el dropdown no salga vacío).
        $subjects = Subject::whereIn('id', function ($q) use ($period) {
            $q->select('subject_id')
                ->from('subject_student')
                ->where('period_id', $period->id);
        })->orderBy('name')->get();

        $selectedSubjectId = $request->query('subject_id');
        if (!$selectedSubjectId) {
            $selectedSubjectId = $subjects->first()->id ?? null;
        }

        $selectedSubject = $selectedSubjectId ? Subject::find($selectedSubjectId) : null;

        // Fechas (domingos) del periodo.
        $sundays = $this->getSundaysForPeriod($period);
        $defaultSunday = $this->getClosestSunday($sundays) ?? ($sundays[0] ?? null);
        $selectedDateRaw = $request->query('class_date', $defaultSunday);
        $selectedDate = $selectedDateRaw ? Carbon::parse($selectedDateRaw)->format('Y-m-d') : null;

        $students = collect();
        $attendanceRecords = collect();
        $attendanceData = [];
        $allSubjectSummaries = collect();

        if ($selectedSubject && $selectedDate) {
            $students = $selectedSubject->studentsForPeriod($period)->get();

            // Asistencia compartida (misma fila para cualquier profesor asignado en el periodo)
            $attendanceRecords = ClassAttendance::where('subject_id', $selectedSubject->id)
                ->where('period_id', $period->id)
                ->whereDate('class_date', $selectedDate)
                ->get()
                ->keyBy('student_id');

            $allAttendanceRecords = ClassAttendance::where('subject_id', $selectedSubject->id)
                ->where('period_id', $period->id)
                ->whereIn('class_date', $sundays)
                ->get();

            // Mapa: attendanceData[student_id][class_date Y-m-d] => record|null
            // Importante: class_date viene casteado a Carbon; al concatenar sin formatear la clave
            // no coincidía con $sunday (string Y-m-d) y el resumen salía vacío.
            $map = $allAttendanceRecords->mapWithKeys(function ($r) {
                $dateKey = $this->normalizeClassDateKey($r->class_date);

                return [$r->student_id . '|' . $dateKey => $r];
            });

            foreach ($students as $student) {
                foreach ($sundays as $sunday) {
                    $attendanceData[$student->id][$sunday] = $map->get($student->id . '|' . $sunday);
                }
            }

        }

        // Resumen global del trimestre: todas las materias con profesor asignado en el periodo.
        $subjectsWithProfessor = Subject::whereIn('id', function ($q) use ($period) {
                $q->select('subject_id')
                    ->from('subject_professor')
                    ->where('period_id', $period->id);
            })
            ->orderByRaw('CASE WHEN Nivel IS NULL OR Nivel = "" THEN 1 ELSE 0 END')
            ->orderBy('Nivel')
            ->orderBy('name')
            ->get();

        $allSubjectSummaries = $subjectsWithProfessor->map(function (Subject $subject) use ($period, $sundays) {
            $courseStudents = $subject->studentsForPeriod($period)->get();
            $courseProfessors = $subject->professorsForPeriod($period)->orderBy('name')->get(['users.id', 'users.name', 'users.email']);

            $allCourseAttendance = ClassAttendance::where('subject_id', $subject->id)
                ->where('period_id', $period->id)
                ->whereIn('class_date', $sundays)
                ->get();

            $map = $allCourseAttendance->mapWithKeys(function ($r) {
                $dateKey = $this->normalizeClassDateKey($r->class_date);
                return [$r->student_id . '|' . $dateKey => $r];
            });

            $courseAttendanceData = [];
            foreach ($courseStudents as $student) {
                foreach ($sundays as $sunday) {
                    $courseAttendanceData[$student->id][$sunday] = $map->get($student->id . '|' . $sunday);
                }
            }

            return [
                'subject' => $subject,
                'professors' => $courseProfessors,
                'students' => $courseStudents,
                'attendanceData' => $courseAttendanceData,
            ];
        });

        return view('admin.period-subject-dashboard', [
            'periods' => $periods,
            'period' => $period,
            'sundays' => $sundays,
            'subjects' => $subjects,
            'selectedSubject' => $selectedSubject,
            'selectedDate' => $selectedDate,
            'students' => $students,
            'attendanceRecords' => $attendanceRecords,
            'attendanceData' => $attendanceData,
            'allSubjectSummaries' => $allSubjectSummaries,
            'currentTrimester' => $period->trimester,
            'currentYear' => $period->year,
        ]);
    }

    public function saveAttendance(Request $request)
    {
        if (!Auth::check() || !Auth::user()->is_admin) {
            abort(403);
        }

        $validated = $request->validate([
            'period_id' => 'required|exists:periods,id',
            'subject_id' => 'required|exists:subjects,id',
            'class_date' => 'required|date',
            'attendance' => 'required|array',
            'attendance.*.student_id' => 'required|exists:users,id',
            'attendance.*.attendance_status' => 'required|in:present,absent,late',
            'attendance.*.bible_verse_delivered' => 'nullable',
            'attendance.*.notes' => 'nullable|string|max:1000',
        ]);

        $period = Period::findOrFail($validated['period_id']);
        $subjectId = (int) $validated['subject_id'];

        $classDate = Carbon::parse($validated['class_date'])->format('Y-m-d');

        DB::beginTransaction();
        try {
            foreach ($validated['attendance'] as $studentPayload) {
                $studentId = (int) $studentPayload['student_id'];

                $bibleDelivered = isset($studentPayload['bible_verse_delivered']) ? true : false;

                ClassAttendance::updateOrCreate(
                    [
                        'subject_id' => $subjectId,
                        'student_id' => $studentId,
                        'class_date' => $classDate,
                        'period_id' => $period->id,
                    ],
                    [
                        'professor_id' => Auth::id(),
                        'attendance_status' => $studentPayload['attendance_status'],
                        'bible_verse_delivered' => $bibleDelivered,
                        'notes' => $studentPayload['notes'] ?? null,
                    ]
                );
            }

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Error al guardar la asistencia');
        }

        return redirect()->route('admin.period-subject-dashboard', [
            'period_id' => $period->id,
            'subject_id' => $subjectId,
            'class_date' => $classDate,
        ]);
    }

    /**
     * Fecha de asistencia siempre como Y-m-d para comparar con domingos del periodo.
     */
    private function normalizeClassDateKey($value): string
    {
        if ($value === null) {
            return '';
        }

        return Carbon::parse($value)->format('Y-m-d');
    }

    private function getSundaysForPeriod(Period $period): array
    {
        $year = (int) $period->year;
        $currentTrimester = (int) $period->trimester;

        $sundays = [];
        $monthRanges = [
            1 => [1, 2, 3, 4],
            2 => [5, 6, 7, 8],
            3 => [9, 10, 11, 12],
        ];

        $months = $monthRanges[$currentTrimester] ?? [];

        foreach ($months as $month) {
            $startDate = new \DateTime("$year-$month-01");
            $endDate = new \DateTime("$year-$month-" . $startDate->format('t'));

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
}

