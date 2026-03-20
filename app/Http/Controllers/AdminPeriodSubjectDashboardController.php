<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Period;
use App\Models\Subject;
use App\Models\User;
use App\Models\ClassAttendance;
use App\Models\Grade;
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

        $professors = collect();
        $selectedProfessorId = $request->query('professor_id');
        if ($selectedSubject) {
            $professors = $selectedSubject->professorsForPeriod($period)->orderBy('name')->get();
            if (!$selectedProfessorId) {
                $selectedProfessorId = $professors->first()->id ?? null;
            }
        }

        $selectedProfessor = $selectedProfessorId ? User::find($selectedProfessorId) : null;

        // Fechas (domingos) del periodo.
        $sundays = $this->getSundaysForPeriod($period);
        $defaultSunday = $this->getClosestSunday($sundays) ?? ($sundays[0] ?? null);
        $selectedDateRaw = $request->query('class_date', $defaultSunday);
        $selectedDate = $selectedDateRaw ? Carbon::parse($selectedDateRaw)->format('Y-m-d') : null;

        $students = collect();
        $attendanceRecords = collect();
        $attendanceData = [];
        $grades = collect();

        if ($selectedSubject && $selectedProfessor && $selectedDate) {
            $students = $selectedSubject->studentsForPeriod($period)->get();

            // Registro para la fila editable (un domingo específico).
            // whereDate + fecha normalizada Y-m-d evita desajustes con tipos DATE/Carbon
            $attendanceRecords = ClassAttendance::where('professor_id', $selectedProfessor->id)
                ->where('subject_id', $selectedSubject->id)
                ->whereDate('class_date', $selectedDate)
                ->get()
                ->keyBy('student_id');

            // Registros para el resumen (todos los domingos del periodo).
            $allAttendanceRecords = ClassAttendance::where('professor_id', $selectedProfessor->id)
                ->where('subject_id', $selectedSubject->id)
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

            // Grades del periodo seleccionado.
            $grades = Grade::where('subject_id', $selectedSubject->id)
                ->where('year', $period->year)
                ->where('trimester', $period->trimester)
                ->get();
        }

        return view('admin.period-subject-dashboard', [
            'periods' => $periods,
            'period' => $period,
            'sundays' => $sundays,
            'subjects' => $subjects,
            'selectedSubject' => $selectedSubject,
            'selectedProfessor' => $selectedProfessor,
            'professors' => $professors,
            'selectedDate' => $selectedDate,
            'students' => $students,
            'attendanceRecords' => $attendanceRecords,
            'attendanceData' => $attendanceData,
            'grades' => $grades,
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
            'professor_id' => 'required|exists:users,id',
            'class_date' => 'required|date',
            'attendance' => 'required|array',
            'attendance.*.student_id' => 'required|exists:users,id',
            'attendance.*.attendance_status' => 'required|in:present,absent,late',
            'attendance.*.bible_verse_delivered' => 'nullable',
            'attendance.*.notes' => 'nullable|string|max:1000',
        ]);

        $period = Period::findOrFail($validated['period_id']);
        $subjectId = (int) $validated['subject_id'];
        $professorId = (int) $validated['professor_id'];

        // Asegurar que el profesor esté asignado a la materia en este periodo.
        $isAssigned = DB::table('subject_professor')
            ->where('subject_id', $subjectId)
            ->where('professor_id', $professorId)
            ->where('period_id', $period->id)
            ->exists();

        if (!$isAssigned) {
            abort(403, 'El profesor no está asignado a la materia en este periodo');
        }

        $classDate = Carbon::parse($validated['class_date'])->format('Y-m-d');

        DB::beginTransaction();
        try {
            foreach ($validated['attendance'] as $studentPayload) {
                $studentId = (int) $studentPayload['student_id'];

                $bibleDelivered = isset($studentPayload['bible_verse_delivered']) ? true : false;

                ClassAttendance::updateOrCreate(
                    [
                        'professor_id' => $professorId,
                        'subject_id' => $subjectId,
                        'student_id' => $studentId,
                        'class_date' => $classDate,
                    ],
                    [
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

        // Volver con filtros aplicados.
        return redirect()->route('admin.period-subject-dashboard', [
            'period_id' => $period->id,
            'subject_id' => $subjectId,
            'professor_id' => $professorId,
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

