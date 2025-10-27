<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\ClassAttendance;
use App\Models\Subject;
use Illuminate\Support\Facades\Log;

class AttendanceController extends Controller
{
    /**
     * Mostrar la vista principal de asistencia
     */



    public function index(Request $request)
    {
        $professor = Auth::user();
        $subjects = $professor->subjects;

        // Obtener domingos del trimestre actual
        $sundays = $this->getSundaysForCurrentTrimester();

        // Obtener el domingo más cercano del trimestre actual
        $defaultSunday = $this->getClosestSundayInCurrentTrimester();

        // Obtener el trimestre actual
        $currentTrimester = $this->getCurrentTrimester();

        $selectedSubject = null;
        $selectedDate = $request->get('class_date', $defaultSunday);
        $students = collect();
        $attendanceRecords = collect();
        $attendanceData = [];

        // Si se seleccionó una materia y fecha
        if ($request->get('subject_id') && $selectedDate) {
            $selectedSubject = Subject::find($request->get('subject_id'));

            if ($selectedSubject && $professor->subjects->contains($selectedSubject)) {
                $students = $selectedSubject->students;

                // Obtener registros de asistencia existentes
                $attendanceRecords = ClassAttendance::where('professor_id', $professor->id)
                    ->where('subject_id', $selectedSubject->id)
                    ->where('class_date', $selectedDate)
                    ->get()
                    ->keyBy('student_id');

                // Obtener todos los registros del trimestre para el resumen
                $allAttendanceRecords = ClassAttendance::where('professor_id', $professor->id)
                    ->where('subject_id', $selectedSubject->id)
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
            'attendanceData' // Agregar esta línea
        ));
    }
    /**
     * Guardar los registros de asistencia
     */
    public function store(Request $request)
    {
        $professor = Auth::user();

        $validated = $request->validate([
            'subject_id' => 'required|integer|exists:subjects,id',
            'class_date' => 'required|date',
            'attendance' => 'required|array',
            'attendance.*.student_id' => 'required|integer|exists:users,id',
            'attendance.*.attendance_status' => 'required|in:present,absent,late',
            'attendance.*.bible_verse_delivered' => 'boolean',
            'attendance.*.notes' => 'nullable|string|max:500'
        ]);

        // Verificar que el profesor tenga acceso a esta materia
        $subject = Subject::find($validated['subject_id']);
        if (!$professor->subjects->contains($subject)) {
            return redirect()->back()->with('error', 'No tienes acceso a esta materia.');
        }

        // Guardar o actualizar registros de asistencia
        foreach ($validated['attendance'] as $attendanceData) {
            ClassAttendance::updateOrCreate(
                [
                    'professor_id' => $professor->id,
                    'subject_id' => $validated['subject_id'],
                    'student_id' => $attendanceData['student_id'],
                    'class_date' => $validated['class_date']
                ],
                [
                    'attendance_status' => $attendanceData['attendance_status'],
                    'bible_verse_delivered' => $attendanceData['bible_verse_delivered'] ?? false,
                    'notes' => $attendanceData['notes'] ?? null
                ]
            );
        }

        // Redirigir manteniendo los parámetros de la materia y fecha
        return redirect()->route('attendance.index', [
            'subject_id' => $validated['subject_id'],
            'class_date' => $validated['class_date']
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
     * Obtener los domingos del trimestre actual
     */
    private function getSundaysForCurrentTrimester()
    {
        $currentTrimester = $this->getCurrentTrimester();
        $year = date('Y');

        $sundays = [];

        // Definir rangos de meses por trimestre
        $monthRanges = [
            1 => [1, 2, 3, 4],    // Enero - Abril
            2 => [5, 6, 7, 8],    // Mayo - Agosto
            3 => [9, 10, 11, 12]   // Septiembre - Diciembre
        ];

        $months = $monthRanges[$currentTrimester];

        foreach ($months as $month) {
            $startDate = new \DateTime("$year-$month-01");
            $endDate = new \DateTime("$year-$month-" . $startDate->format('t'));

            // Encontrar el primer domingo del mes
            while ($startDate->format('N') != 7) {
                $startDate->add(new \DateInterval('P1D'));
            }

            // Generar todos los domingos del mes
            while ($startDate <= $endDate) {
                $sundays[] = $startDate->format('Y-m-d');
                $startDate->add(new \DateInterval('P7D'));
            }
        }

        return $sundays;
    }

    /**
     * Obtener el domingo más cercano del trimestre actual
     */
    private function getClosestSundayInCurrentTrimester()
    {
        $sundays = $this->getSundaysForCurrentTrimester();
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
