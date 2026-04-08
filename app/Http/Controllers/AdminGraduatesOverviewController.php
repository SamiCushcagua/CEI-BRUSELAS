<?php

namespace App\Http\Controllers;

use App\Models\Grade;
use App\Models\Period;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminGraduatesOverviewController extends Controller
{
    public function index()
    {
        if (! Auth::check() || ! Auth::user()->is_admin) {
            abort(403);
        }

        $students = User::query()
            ->where('is_profesor', false)
            ->where('is_admin', false)
            ->orderBy('name')
            ->get();

        $subjects = Subject::query()->orderBy('name')->get();

        $gradesOrdered = Grade::query()
            ->orderByDesc('year')
            ->orderByDesc('trimester')
            ->orderByDesc('id')
            ->get();

        $latestGrades = [];
        foreach ($gradesOrdered as $g) {
            $k = (int) $g->student_id.'-'.(int) $g->subject_id;
            if (! isset($latestGrades[$k])) {
                $latestGrades[$k] = $g;
            }
        }

        return view('graduados_overview.index', [
            'students' => $students,
            'subjects' => $subjects,
            'latestGrades' => $latestGrades,
        ]);
    }

    /**
     * Actualiza "aprobó" en el último registro de calificación (alumno × materia), plan B.
     */
    public function updatePass(Request $request)
    {
        if (! Auth::check() || ! Auth::user()->is_admin) {
            abort(403);
        }

        $data = $request->validate([
            'student_id' => 'required|integer|exists:users,id',
            'subject_id' => 'required|integer|exists:subjects,id',
            'passed' => 'required|boolean',
        ]);

        $student = User::query()->findOrFail($data['student_id']);
        if (! $student->isStudent()) {
            return response()->json(['success' => false, 'message' => 'El usuario no es estudiante.'], 422);
        }

        $grade = Grade::query()
            ->where('student_id', $data['student_id'])
            ->where('subject_id', $data['subject_id'])
            ->orderByDesc('year')
            ->orderByDesc('trimester')
            ->orderByDesc('id')
            ->first();

        if ($grade) {
            $grade->update(['passed' => $data['passed']]);

            return response()->json(['success' => true]);
        }

        if ($data['passed']) {
            $period = Period::active()->first()
                ?? Period::query()->orderByDesc('year')->orderByDesc('trimester')->first();

            if (! $period) {
                return response()->json([
                    'success' => false,
                    'message' => 'No hay periodo configurado; no se puede crear una calificación.',
                ], 422);
            }

            Grade::create([
                'student_id' => $data['student_id'],
                'subject_id' => $data['subject_id'],
                'year' => (int) $period->year,
                'trimester' => (int) $period->trimester,
                'passed' => true,
            ]);

            return response()->json(['success' => true]);
        }

        return response()->json(['success' => true]);
    }
}
