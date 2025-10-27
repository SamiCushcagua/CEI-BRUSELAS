<?php

namespace App\Http\Controllers;

use App\Models\Grade;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class GradeReportController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Mostrar reportes de calificaciones
     */
 /**
 * Mostrar reportes de calificaciones
 */
public function index()
{
    $subjects = Subject::whereHas('professors', function($query) {
        $query->where('professor_id', Auth::id());
    })->get();
    
    $currentTrimester = $this->getCurrentTrimester();
    
    return view('grade-reports.index', compact('subjects', 'currentTrimester'));
}

    /**
     * Generar reporte de calificaciones
     */
    public function generate(Request $request)
    {
        $request->validate([
            'subject_id' => 'required|exists:subjects,id',
            'year' => 'nullable|integer',
            'trimester' => 'nullable|integer|min:1|max:3',
            'student_id' => 'nullable|exists:users,id'
        ]);

        $subject = Subject::findOrFail($request->subject_id);
        
        // Verificar permisos
        if (!$subject->professors()->where('professor_id', Auth::id())->exists()) {
            abort(403, 'No tienes permisos para generar este reporte');
        }

        $year = $request->year ?? date('Y');
        $trimester = $request->trimester ?? $this->getCurrentTrimester();

        $query = Grade::where('subject_id', $request->subject_id)
                     ->where('year', $year)
                     ->where('trimester', $trimester)
                     ->with(['student', 'subject']);

        if ($request->student_id) {
            $query->where('student_id', $request->student_id);
        }

        $grades = $query->get();

        $statistics = [
            'total_students' => $grades->count(),
            'average_score' => $grades->avg('total_score'),
            'highest_score' => $grades->max('total_score'),
            'lowest_score' => $grades->min('total_score'),
            'passing_students' => $grades->where('total_score', '>=', 70)->count(),
            'failing_students' => $grades->where('total_score', '<', 70)->count(),
        ];

        return view('grade-reports.show', compact('subject', 'grades', 'statistics', 'year', 'trimester'));
    }

    /**
     * Obtener trimestre actual
     */
    private function getCurrentTrimester()
    {
        $month = date('n');
        
        if ($month >= 1 && $month <= 4) {
            return 1;
        } elseif ($month >= 5 && $month <= 8) {
            return 2;
        } else {
            return 3;
        }
    }
}