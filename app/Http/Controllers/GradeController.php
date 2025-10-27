<?php

namespace App\Http\Controllers;

use App\Models\Grade;
use App\Models\Subject;
use App\Models\User;
use App\Models\EvaluationType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class GradeController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Mostrar lista de materias para calificaciones
     */
    public function index()
    {
        $subjects = Subject::whereHas('professors', function($query) {
            $query->where('professor_id', Auth::id());
        })->with(['students', 'grades'])->get();
        
        return view('grades.index', compact('subjects'));
    }

    /**
     * Mostrar calificaciones de una materia específica
     */
    public function show(Subject $subject)
    {
        // Verificar que el usuario es profesor de esta materia
        if (!$subject->professors()->where('professor_id', Auth::id())->exists()) {
            abort(403, 'No tienes permisos para ver estas calificaciones');
        }

        $students = $subject->students()->get();
        $currentYear = date('Y');
        $currentTrimester = $this->getCurrentTrimester();
        
        // Obtener calificaciones agrupadas por estudiante

      $grades = Grade::where('subject_id', $subject->id)
      ->where('year', $currentYear)
      ->where('trimester', $currentTrimester)
      ->get();
        
        // Obtener tipos de evaluación
        $evaluationTypes = EvaluationType::active()->get();
        
        return view('grades.show', compact('subject', 'students', 'grades', 'evaluationTypes', 'currentYear', 'currentTrimester'));
    }

    /**
     * Almacenar nueva calificación
     */
    public function store(Request $request)
    {
        $request->validate([
            'student_id' => 'required|exists:users,id',
            'subject_id' => 'required|exists:subjects,id',
            'trimester' => 'required|integer|min:1|max:3',
            'year' => 'required|integer',
            'task_score' => 'nullable|numeric|min:0|max:100',
            'exam_score1' => 'nullable|numeric|min:0|max:100',
            'exam_score2' => 'nullable|numeric|min:0|max:100',
            'participation_score' => 'nullable|numeric|min:0|max:100',
            'bible_score' => 'nullable|numeric|min:0|max:100',
            'text_score' => 'nullable|numeric|min:0|max:100',
            'other_score' => 'nullable|numeric|min:0|max:100',
            'notes' => 'nullable|string|max:1000'
        ]);

        // Verificar que el usuario es profesor de esta materia
        $subject = Subject::findOrFail($request->subject_id);
        if (!$subject->professors()->where('professor_id', Auth::id())->exists()) {
            abort(403, 'No tienes permisos para calificar esta materia');
        }

        try {
            DB::beginTransaction();

            $grade = Grade::updateOrCreate(
                [
                    'student_id' => $request->student_id,
                    'subject_id' => $request->subject_id,
                    'trimester' => $request->trimester,
                    'year' => $request->year
                ],
                $request->only([
                    'task_score', 'exam_score1', 'exam_score2', 'participation_score',
                    'bible_score', 'text_score', 'other_score', 'notes'
                ])
            );

            DB::commit();

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Calificación guardada exitosamente',
                    'grade' => $grade
                ]);
            }

            return redirect()->back()->with('success', 'Calificación guardada exitosamente');

        } catch (\Exception $e) {
            DB::rollBack();
            
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error al guardar la calificación: ' . $e->getMessage()
                ], 500);
            }

            return redirect()->back()->with('error', 'Error al guardar la calificación');
        }
    }

    /**
     * Actualizar calificación existente
     */
    public function update(Request $request, Grade $grade)
    {
        // Verificar que el usuario es profesor de esta materia
        if (!$grade->subject->professors()->where('professor_id', Auth::id())->exists()) {
            abort(403, 'No tienes permisos para modificar esta calificación');
        }

        $request->validate([
            'task_score' => 'nullable|numeric|min:0|max:100',
            'exam_score1' => 'nullable|numeric|min:0|max:100',
            'exam_score2' => 'nullable|numeric|min:0|max:100',
            'participation_score' => 'nullable|numeric|min:0|max:100',
            'bible_score' => 'nullable|numeric|min:0|max:100',
            'text_score' => 'nullable|numeric|min:0|max:100',
            'other_score' => 'nullable|numeric|min:0|max:100',
            'notes' => 'nullable|string|max:1000'
        ]);

        try {
            $grade->update($request->only([
                'task_score', 'exam_score1', 'exam_score2 ', 'participation_score',
                'bible_score', 'text_score', 'other_score', 'notes'
            ]));

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Calificación actualizada exitosamente',
                    'grade' => $grade
                ]);
            }

            return redirect()->back()->with('success', 'Calificación actualizada exitosamente');

        } catch (\Exception $e) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error al actualizar la calificación: ' . $e->getMessage()
                ], 500);
            }

            return redirect()->back()->with('error', 'Error al actualizar la calificación');
        }
    }

    /**
     * Eliminar calificación
     */
    public function destroy(Grade $grade)
    {
        // Verificar que el usuario es profesor de esta materia
        if (!$grade->subject->professors()->where('professor_id', Auth::id())->exists()) {
            abort(403, 'No tienes permisos para eliminar esta calificación');
        }

        try {
            $grade->delete();

            if (request()->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Calificación eliminada exitosamente'
                ]);
            }

            return redirect()->back()->with('success', 'Calificación eliminada exitosamente');

        } catch (\Exception $e) {
            if (request()->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error al eliminar la calificación: ' . $e->getMessage()
                ], 500);
            }

            return redirect()->back()->with('error', 'Error al eliminar la calificación');
        }
    }

    /**
     * Obtener calificaciones por estudiante
     */
    public function getStudentGrades(Request $request)
    {
        $request->validate([
            'student_id' => 'required|exists:users,id',
            'subject_id' => 'required|exists:subjects,id',
            'year' => 'nullable|integer',
            'trimester' => 'nullable|integer|min:1|max:3'
        ]);

        $query = Grade::where('student_id', $request->student_id)
                     ->where('subject_id', $request->subject_id);

        if ($request->year) {
            $query->where('year', $request->year);
        }

        if ($request->trimester) {
            $query->where('trimester', $request->trimester);
        }

        $grades = $query->get();

        return response()->json([
            'success' => true,
            'grades' => $grades
        ]);
    }

    /**
     * Obtener estadísticas de calificaciones
     */
    public function getStatistics(Request $request)
    {
        $request->validate([
            'subject_id' => 'required|exists:subjects,id',
            'year' => 'nullable|integer',
            'trimester' => 'nullable|integer|min:1|max:3'
        ]);

        $query = Grade::where('subject_id', $request->subject_id);

        if ($request->year) {
            $query->where('year', $request->year);
        }

        if ($request->trimester) {
            $query->where('trimester', $request->trimester);
        }

        $grades = $query->get();

        $statistics = [
            'total_students' => $grades->count(),
         'average_score' => $grades->avg('average_score'),
            'highest_score' => $grades->max('total_score'),
            'lowest_score' => $grades->min('total_score'),
            'passing_students' => $grades->where('total_score', '>=', 70)->count(),
            'failing_students' => $grades->where('total_score', '<', 70)->count(),
        ];

        return response()->json([
            'success' => true,
            'statistics' => $statistics
        ]);
    }

    /**
     * Exportar calificaciones a PDF
     */
    public function exportPdf(Request $request)
    {
        $request->validate([
            'subject_id' => 'required|exists:subjects,id',
            'year' => 'nullable|integer',
            'trimester' => 'nullable|integer|min:1|max:3'
        ]);

        $subject = Subject::findOrFail($request->subject_id);
        
        // Verificar permisos
        if (!$subject->professors()->where('professor_id', Auth::id())->exists()) {
            abort(403, 'No tienes permisos para exportar estas calificaciones');
        }

        $students = $subject->students()->get();
        $year = $request->year ?? date('Y');
        $trimester = $request->trimester ?? $this->getCurrentTrimester();

        $grades = Grade::where('subject_id', $request->subject_id)
                      ->where('year', $year)
                      ->where('trimester', $trimester)
                      ->get()
                      ->groupBy('student_id');

        // Aquí puedes implementar la generación de PDF
        // Por ahora retornamos una vista
        return view('grades.export', compact('subject', 'students', 'grades', 'year', 'trimester'));
    }

    /**
     * Obtener trimestre actual
     */
    private function getCurrentTrimester()
    {
        $month = date('n');
        
        if ($month >= 1 && $month <= 4) {
            return 1; // Primer trimestre
        } elseif ($month >= 5 && $month <= 8) {
            return 2; // Segundo trimestre
        } else {
            return 3; // Tercer trimestre
        }
    }
}